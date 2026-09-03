<?php

declare(strict_types=1);

namespace Fleetbase\Sdk\Test\Contract;

use Fleetbase\Sdk\Service;
use Fleetbase\Sdk\Test\TestCase;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use ReflectionClass;

final class EndpointContractTest extends TestCase
{
    public function testEveryEndpointContract(): void
    {
        foreach (self::endpointCases() as $case) {
            [$serviceClass, $method, $httpMethod, $urlTemplate, $parameters, $requestFixture] = $case;
            $options = [];
            $query = self::normalizeFixture($requestFixture['query'] ?? []);
            if (is_array($query) && $query !== []) {
                $options['query'] = $query;
            }
            $bodyType = $requestFixture['body_type'] ?? null;
            $body = self::normalizeFixture($requestFixture['body'] ?? null);
            $expectedBody = null;
            $expectedMultipart = [];
            if ($bodyType === 'json' && is_array($body)) {
                $parameters['body'] = $body;
                $expectedBody = $body;
            } elseif ($bodyType === 'formdata' && is_array($body)) {
                foreach ($body as $part) {
                    if (!is_array($part) || !is_string($part['key'] ?? null)) {
                        continue;
                    }
                    $normalizedValue = self::normalizeFixture($part['value'] ?? '');
                    $contents = ($part['type'] ?? null) === 'file'
                        ? 'fixture-file-content'
                        : (is_scalar($normalizedValue) ? (string) $normalizedValue : '');
                    $options['multipart'][] = ['name' => $part['key'], 'contents' => $contents];
                    $expectedMultipart[$part['key']] = $contents;
                }
            }
            $client = $this->mockHttpClient([new Response(200, ['Content-Type' => 'application/json'], '{}')]);
            $reflection = new ReflectionClass($serviceClass);
            $service = $reflection->newInstance($client);
            $service->{$method}($parameters, $options);

            $transaction = $this->history[0] ?? null;
            self::assertIsArray($transaction);
            $request = $transaction['request'] ?? null;
            self::assertInstanceOf(RequestInterface::class, $request);
            self::assertSame($httpMethod, $request->getMethod());

            $expected = preg_replace('#^\{\{base_url\}\}/\{\{namespace\}\}/?#i', '', $urlTemplate);
            self::assertIsString($expected);
            foreach ($parameters as $name => $value) {
                if ($name === 'body') {
                    continue;
                }
                if (!is_scalar($value)) {
                    throw new \RuntimeException('Endpoint path parameters must be scalar.');
                }
                $expected = str_replace('{{' . $name . '}}', rawurlencode((string) $value), $expected);
                $expected = preg_replace('/:' . preg_quote($name, '/') . '(?![A-Za-z0-9_-])/', rawurlencode((string) $value), $expected);
                self::assertIsString($expected);
            }
            if (is_array($query) && $query !== []) {
                $expected .= (strpos($expected, '?') === false ? '?' : '&')
                    . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
            }
            self::assertSame('/v1/' . ltrim($expected, '/'), $request->getUri()->getPath()
                . ($request->getUri()->getQuery() !== '' ? '?' . $request->getUri()->getQuery() : ''));
            if (is_array($expectedBody)) {
                self::assertSame($expectedBody, json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR));
            }
            foreach ($expectedMultipart as $name => $contents) {
                self::assertStringContainsString('name="' . $name . '"', (string) $request->getBody());
                self::assertStringContainsString($contents, (string) $request->getBody());
            }
        }
    }

    /** @return iterable<string, array{class-string<Service>, string, string, string, array<string, scalar>, array<string, mixed>}> */
    private static function endpointCases(): iterable
    {
        $contents = file_get_contents(dirname(__DIR__, 2) . '/contracts/postman-manifest.json');
        if (!is_string($contents)) {
            throw new \RuntimeException('Unable to read the endpoint contract manifest.');
        }
        $manifest = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($manifest) || !is_array($manifest['requests'] ?? null)) {
            throw new \RuntimeException('The endpoint contract manifest is invalid.');
        }
        foreach ($manifest['requests'] as $request) {
            if (!is_array($request)) {
                throw new \RuntimeException('The endpoint contract request is invalid.');
            }
            $id = $request['id'] ?? null;
            $implementationName = $request['implementation'] ?? null;
            $httpMethod = $request['method'] ?? null;
            $url = $request['url'] ?? null;
            $requestFixture = $request['request_fixture'] ?? null;
            if (!is_string($id) || !is_string($implementationName) || !is_string($httpMethod) || !is_string($url) || !is_array($requestFixture)) {
                throw new \RuntimeException('The endpoint contract request fields are invalid.');
            }
            $implementation = explode('::', $implementationName, 2);
            $serviceClass = $implementation[0] ?? '';
            $serviceMethod = $implementation[1] ?? '';
            if (!class_exists($serviceClass) || !is_subclass_of($serviceClass, Service::class) || !method_exists($serviceClass, $serviceMethod)) {
                throw new \RuntimeException('The endpoint contract implementation is invalid.');
            }
            $normalizedRequestFixture = [];
            foreach ($requestFixture as $fixtureKey => $fixtureValue) {
                if (is_string($fixtureKey)) {
                    $normalizedRequestFixture[$fixtureKey] = $fixtureValue;
                }
            }
            $parameters = [];
            preg_match_all('/(?:\{\{([^}]+)\}\}|:([A-Za-z][A-Za-z0-9_-]*))/', $url, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $bracedName = $match[1] ?? '';
                $colonName = $match[2] ?? '';
                $name = $bracedName !== '' ? $bracedName : $colonName;
                if (!in_array($name, ['base_url', 'namespace'], true)) {
                    $parameters[$name] = $name . '-fixture';
                }
            }
            yield $id => [
                $serviceClass,
                $serviceMethod,
                $httpMethod,
                $url,
                $parameters,
                $normalizedRequestFixture,
            ];
        }
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function normalizeFixture($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = self::normalizeFixture($item);
            }
            return $value;
        }
        if (!is_string($value)) {
            return $value;
        }

        return preg_replace_callback('/\{\{([^}]+)\}\}/', static function (array $matches): string {
            return trim($matches[1], '$') . '-fixture';
        }, $value) ?? $value;
    }
}
