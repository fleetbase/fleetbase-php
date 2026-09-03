<?php

/**
 * This file is part of the fleetbase/fleetbase-php library.
 *
 * @copyright Copyright (c) Fleetbase Pte Ltd. <ron@fleetbase.io>
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk;

use Fleetbase\Sdk\Exception\AuthenticationException;
use Fleetbase\Sdk\Exception\AuthorizationException;
use Fleetbase\Sdk\Exception\ConflictException;
use Fleetbase\Sdk\Exception\DecodingException;
use Fleetbase\Sdk\Exception\NotFoundException;
use Fleetbase\Sdk\Exception\RateLimitException;
use Fleetbase\Sdk\Exception\ServerException;
use Fleetbase\Sdk\Exception\TimeoutException;
use Fleetbase\Sdk\Exception\TransportException;
use Fleetbase\Sdk\Exception\UnexpectedResponseException;
use Fleetbase\Sdk\Exception\ValidationException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Throwable;

class HttpClient
{
    private string $host;
    private string $namespace;
    private string $apiKey;

    /** @var array<string, mixed> */
    private array $options = [];

    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    private Response $lastResponse;
    private ?ResponseInterface $lastPsrResponse = null;

    /** @param array<string, mixed> $options */
    public function __construct(array $options = [])
    {
        $configuration = new Configuration(
            is_string($options['publicKey'] ?? null) ? $options['publicKey'] : '',
            $options,
            (bool) ($options['debug'] ?? false)
        );

        $this->options = $configuration->toArray();
        $this->host = $configuration->getHost();
        $this->namespace = $configuration->getNamespace();
        $this->apiKey = $configuration->getApiKey();
        $this->requestFactory = $this->resolveRequestFactory($options);
        $this->streamFactory = $this->resolveStreamFactory($options);
        $this->client = $this->resolveClient($options);
    }

    public function setHost(string $host): HttpClient
    {
        $configuration = new Configuration(
            $this->apiKey,
            array_merge($this->options, ['host' => $host]),
            (bool) ($this->options['debug'] ?? false)
        );
        $this->host = $configuration->getHost();
        $this->options = $configuration->toArray();

        return $this;
    }

    public function setNamespace(string $namespace): HttpClient
    {
        $configuration = new Configuration(
            $this->apiKey,
            array_merge($this->options, ['namespace' => $namespace]),
            (bool) ($this->options['debug'] ?? false)
        );
        $this->namespace = $configuration->getNamespace();
        $this->options = $configuration->toArray();

        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /** @return array<string, mixed> */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getLastResponse(): Response
    {
        if (!isset($this->lastResponse)) {
            throw new UnexpectedResponseException('No Fleetbase response has been received.');
        }

        return $this->lastResponse;
    }

    public function getLastPsrResponse(): ?ResponseInterface
    {
        return $this->lastPsrResponse;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function request(string $method, string $path, array $data = [], array $options = [])
    {
        $method = strtoupper($method);
        $request = $this->buildRequest($method, $path, $data, $options);

        if (isset($options['onBefore']) && is_callable($options['onBefore'])) {
            call_user_func($options['onBefore']);
        }

        try {
            $response = $this->sendWithRetries($request, $options);
        } catch (ConnectException $exception) {
            $message = stripos($exception->getMessage(), 'timed out') !== false
                ? 'The Fleetbase request timed out.'
                : 'Unable to connect to the Fleetbase API.';
            $class = stripos($exception->getMessage(), 'timed out') !== false
                ? TimeoutException::class
                : TransportException::class;
            throw new $class($message, null, null, [], null, $method, $this->sanitizeUrl((string) $request->getUri()), $exception);
        } catch (ClientExceptionInterface $exception) {
            throw new TransportException(
                'The Fleetbase transport failed.',
                null,
                null,
                [],
                null,
                $method,
                $this->sanitizeUrl((string) $request->getUri()),
                $exception
            );
        }

        $this->lastPsrResponse = $response;
        $this->lastResponse = $this->normalizeResponse($response);
        $decoded = $this->decodeResponse($response, $method, (string) $request->getUri());

        if (isset($options['onAfter']) && is_callable($options['onAfter'])) {
            call_user_func($options['onAfter'], $decoded);
        }

        return $decoded;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $options
     * @return mixed
     */
    public function post(string $path, array $data = [], $options = [])
    {
        return $this->request('POST', $path, $data, $this->normalizeOptions($options));
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $options
     * @return mixed
     */
    public function get(string $path, array $data = [], $options = [])
    {
        return $this->request('GET', $path, $data, $this->normalizeOptions($options));
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $options
     * @return mixed
     */
    public function delete(string $path, array $data = [], $options = [])
    {
        return $this->request('DELETE', $path, $data, $this->normalizeOptions($options));
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $options
     * @return mixed
     */
    public function put(string $path, array $data = [], $options = [])
    {
        return $this->request('PUT', $path, $data, $this->normalizeOptions($options));
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $options
     * @return mixed
     */
    public function patch(string $path, array $data = [], $options = [])
    {
        return $this->request('PATCH', $path, $data, $this->normalizeOptions($options));
    }

    /** @param array<string, mixed> $options */
    private function resolveClient(array $options): ClientInterface
    {
        $client = $options['httpClient'] ?? $options['client'] ?? null;
        if ($client instanceof ClientInterface) {
            return $client;
        }

        return new Client(['http_errors' => false]);
    }

    /** @param array<string, mixed> $options */
    private function resolveRequestFactory(array $options): RequestFactoryInterface
    {
        $factory = $options['requestFactory'] ?? null;
        return $factory instanceof RequestFactoryInterface ? $factory : new HttpFactory();
    }

    /** @param array<string, mixed> $options */
    private function resolveStreamFactory(array $options): StreamFactoryInterface
    {
        $factory = $options['streamFactory'] ?? null;
        return $factory instanceof StreamFactoryInterface ? $factory : new HttpFactory();
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     */
    private function buildRequest(string $method, string $path, array $data, array $options): RequestInterface
    {
        $url = $this->buildRequestUrl($path);
        if (in_array($method, ['GET', 'HEAD'], true) && $data !== []) {
            $separator = strpos($url, '?') === false ? '?' : '&';
            $url .= $separator . http_build_query($data, '', '&', PHP_QUERY_RFC3986);
        }

        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
            'User-Agent' => 'fleetbase-php/1.1.1 PHP/' . PHP_VERSION,
        ];
        if (is_array($options['headers'] ?? null)) {
            foreach ($options['headers'] as $name => $value) {
                if (is_string($name) && is_string($value)) {
                    $headers[$name] = $value;
                } elseif (is_string($name) && is_array($value)) {
                    $headerValues = array_values(array_filter($value, 'is_string'));
                    $headers[$name] = $headerValues;
                }
            }
        }
        if (isset($options['idempotency_key']) && is_string($options['idempotency_key'])) {
            $headers['Idempotency-Key'] = $options['idempotency_key'];
        }

        $request = $this->requestFactory->createRequest($method, $url);
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if (isset($options['multipart']) && is_array($options['multipart'])) {
            $stream = new MultipartStream($options['multipart']);
            return $request
                ->withHeader('Content-Type', 'multipart/form-data; boundary=' . $stream->getBoundary())
                ->withBody($stream);
        }

        if (isset($options['body'])) {
            if (!is_string($options['body'])
                && !(is_object($options['body']) && method_exists($options['body'], '__toString'))) {
                throw new \InvalidArgumentException('Raw request bodies must be strings or stringable objects.');
            }
            $body = (string) $options['body'];
            return $request->withBody($this->streamFactory->createStream($body));
        }

        if (isset($options['form_params']) && is_array($options['form_params'])) {
            $body = http_build_query($options['form_params'], '', '&', PHP_QUERY_RFC3986);
            return $request
                ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
                ->withBody($this->streamFactory->createStream($body));
        }

        if (!in_array($method, ['GET', 'HEAD'], true) && $data !== []) {
            $json = json_encode($data, JSON_THROW_ON_ERROR);
            return $request
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream($json));
        }

        return $request;
    }

    /** @param array<string, mixed> $options */
    private function send(RequestInterface $request, array $options): ResponseInterface
    {
        if ($this->client instanceof GuzzleClientInterface) {
            $transportOptions = [];
            foreach (['timeout', 'connect_timeout', 'allow_redirects', 'verify', 'proxy'] as $key) {
                if (array_key_exists($key, $options)) {
                    $transportOptions[$key] = $options[$key];
                }
            }
            $transportOptions['http_errors'] = false;
            return $this->client->send($request, $transportOptions);
        }

        return $this->client->sendRequest($request);
    }

    /** @param array<string, mixed> $options */
    private function sendWithRetries(RequestInterface $request, array $options): ResponseInterface
    {
        $configuredRetries = $options['max_retries'] ?? $this->options['max_retries'] ?? 0;
        if (!is_int($configuredRetries) || $configuredRetries < 0) {
            throw new \InvalidArgumentException('The Fleetbase retry count must be a non-negative integer.');
        }

        return $this->sendAttempt($request, $options, $configuredRetries, 0);
    }

    /** @param array<string, mixed> $options */
    private function sendAttempt(RequestInterface $request, array $options, int $configuredRetries, int $attempt): ResponseInterface
    {
        try {
            $response = $this->send($request, $options);
        } catch (ClientExceptionInterface $exception) {
            if ($attempt >= $configuredRetries || !$this->isRetryableRequest($request)) {
                throw $exception;
            }

            ++$attempt;
            $this->waitBeforeRetry($attempt, null, $options);

            return $this->sendAttempt($request, $options, $configuredRetries, $attempt);
        }

        if ($attempt >= $configuredRetries
            || !$this->isRetryableRequest($request)
            || !$this->isRetryableStatus($response->getStatusCode())) {
            return $response;
        }

        ++$attempt;
        $this->waitBeforeRetry($attempt, $response, $options);

        return $this->sendAttempt($request, $options, $configuredRetries, $attempt);
    }

    private function isRetryableRequest(RequestInterface $request): bool
    {
        if ($request->hasHeader('Idempotency-Key')) {
            return true;
        }

        return in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS', 'PUT', 'DELETE'], true);
    }

    private function isRetryableStatus(int $status): bool
    {
        return $status === 429 || $status >= 500;
    }

    /** @param array<string, mixed> $options */
    private function waitBeforeRetry(int $attempt, ?ResponseInterface $response, array $options): void
    {
        $milliseconds = $this->retryAfterMilliseconds($response);
        if ($milliseconds === null) {
            $configuredDelay = $options['retry_delay_ms'] ?? $this->options['retry_delay_ms'] ?? 100;
            if (!is_int($configuredDelay) || $configuredDelay < 0) {
                throw new \InvalidArgumentException('The Fleetbase retry delay must be a non-negative integer.');
            }
            $milliseconds = min(30000, $configuredDelay * (1 << min(8, $attempt - 1)));
        }

        $sleeper = $options['retry_sleep'] ?? $this->options['retry_sleep'] ?? null;
        if (is_callable($sleeper)) {
            call_user_func($sleeper, $milliseconds, $attempt);
            return;
        }

        if ($milliseconds > 0) {
            usleep($milliseconds * 1000);
        }
    }

    private function retryAfterMilliseconds(?ResponseInterface $response): ?int
    {
        if (!$response instanceof ResponseInterface) {
            return null;
        }
        $retryAfter = trim($response->getHeaderLine('Retry-After'));
        if ($retryAfter === '') {
            return null;
        }
        if (ctype_digit($retryAfter)) {
            return min(30000, ((int) $retryAfter) * 1000);
        }

        $timestamp = strtotime($retryAfter);
        if ($timestamp === false) {
            return null;
        }

        return min(30000, max(0, ($timestamp - time()) * 1000));
    }

    /** @return mixed */
    private function decodeResponse(ResponseInterface $response, string $method, string $url)
    {
        $status = $response->getStatusCode();
        $contents = (string) $response->getBody();
        $decoded = null;

        if ($contents !== '') {
            try {
                $decoded = json_decode($contents, false, 512, JSON_THROW_ON_ERROR);
            } catch (Throwable $exception) {
                if ($status >= 400 || stripos($response->getHeaderLine('Content-Type'), 'json') !== false) {
                    throw new DecodingException(
                        'Fleetbase returned malformed JSON.',
                        $status,
                        null,
                        [],
                        $this->requestId($response),
                        $method,
                        $this->sanitizeUrl($url),
                        $exception
                    );
                }

                return $contents;
            }
        }

        if ($status >= 400) {
            $this->throwForError($response, $decoded, $method, $url);
        }

        return $decoded;
    }

    /** @param mixed $decoded */
    private function throwForError(ResponseInterface $response, $decoded, string $method, string $url): void
    {
        $status = $response->getStatusCode();
        $payload = is_object($decoded) ? $this->objectToArray($decoded) : [];
        $message = $this->extractString($payload, ['message', 'error', 'detail'])
            ?? sprintf('Fleetbase API request failed with HTTP %d.', $status);
        $code = $this->extractString($payload, ['code', 'error_code']);
        $errorDetails = $payload['errors'] ?? null;
        if (is_object($errorDetails)) {
            $details = $this->objectToArray($errorDetails);
        } elseif (is_array($errorDetails)) {
            $details = $this->stringKeyedArray($errorDetails);
        } else {
            $details = [];
        }
        $class = $this->exceptionClassForStatus($status);

        throw new $class(
            $message,
            $status,
            $code,
            $details,
            $this->requestId($response),
            $method,
            $this->sanitizeUrl($url)
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<int, string> $keys
     */
    private function extractString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($payload[$key]) && is_string($payload[$key])) {
                return $payload[$key];
            }
        }

        return null;
    }

    /** @return class-string<FleetbaseException> */
    private function exceptionClassForStatus(int $status): string
    {
        if ($status === 401) {
            return AuthenticationException::class;
        }
        if ($status === 403) {
            return AuthorizationException::class;
        }
        if ($status === 404) {
            return NotFoundException::class;
        }
        if ($status === 409) {
            return ConflictException::class;
        }
        if ($status === 422) {
            return ValidationException::class;
        }
        if ($status === 429) {
            return RateLimitException::class;
        }
        if ($status >= 500) {
            return ServerException::class;
        }

        return UnexpectedResponseException::class;
    }

    private function requestId(ResponseInterface $response): ?string
    {
        foreach (['X-Request-Id', 'X-Fleetbase-Request-Id', 'Request-Id'] as $header) {
            $value = $response->getHeaderLine($header);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function normalizeResponse(ResponseInterface $response): Response
    {
        if ($response instanceof Response) {
            return $response;
        }

        return new Response(
            $response->getStatusCode(),
            $response->getHeaders(),
            (string) $response->getBody(),
            $response->getProtocolVersion(),
            $response->getReasonPhrase()
        );
    }

    private function buildRequestUrl(string $path = ''): string
    {
        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        $base = rtrim($this->host, '/');
        if ($this->namespace !== '') {
            $base .= '/' . trim($this->namespace, '/');
        }

        return $base . '/' . ltrim($path, '/');
    }

    private function sanitizeUrl(string $url): string
    {
        $parts = parse_url($url);
        if ($parts === false) {
            return '[invalid-url]';
        }

        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '';

        return $scheme . $host . $port . $path;
    }

    /**
     * @param mixed $options
     * @return array<string, mixed>
     */
    private function normalizeOptions($options): array
    {
        if ($options === null) {
            return [];
        }
        if (!is_array($options)) {
            throw new \InvalidArgumentException('HTTP request options must be an array.');
        }

        $normalized = [];
        foreach ($options as $key => $value) {
            if (is_string($key)) {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    /** @return array<string, mixed> */
    private function objectToArray(object $value): array
    {
        $result = [];
        foreach (get_object_vars($value) as $key => $item) {
            if (is_string($key)) {
                $result[$key] = $item;
            }
        }

        return $result;
    }

    /**
     * @param array<mixed, mixed> $value
     * @return array<string, mixed>
     */
    private function stringKeyedArray(array $value): array
    {
        $result = [];
        foreach ($value as $key => $item) {
            if (is_string($key)) {
                $result[$key] = $item;
            }
        }

        return $result;
    }
}
