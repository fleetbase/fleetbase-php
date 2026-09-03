<?php

declare(strict_types=1);

/**
 * Generate reviewed-shape service traits/classes and complete manifest mappings.
 *
 * The public method identity comes from the locked Postman request name, while
 * the verb and path are copied exactly from the generated manifest.
 */

$options = getopt('', ['manifest:', 'source:', 'tests:']);
$manifestPath = is_string($options['manifest'] ?? null) ? $options['manifest'] : 'contracts/postman-manifest.json';
$sourceRoot = is_string($options['source'] ?? null) ? rtrim($options['source'], '/') : 'src/Services';
$testPath = is_string($options['tests'] ?? null) ? $options['tests'] : 'tests/Contract/EndpointContractTest.php';

require dirname(__DIR__) . '/vendor/autoload.php';

$manifest = readManifest($manifestPath);
$requests = $manifest['requests'];
if (!is_array($requests)) {
    fail('Manifest requests must be an array.');
}

$groups = [];
foreach ($requests as $index => $request) {
    if (!is_array($request)) {
        fail(sprintf('Manifest request %d is invalid.', $index + 1));
    }
    $group = requiredString($request, 'group');
    $service = serviceName($group);
    $method = methodName(requiredString($request, 'name'));
    $request['sdk_signature'] = signatureForRequest($request);
    $key = $service . '::' . $method;
    if (isset($groups[$service]['methods'][$method])) {
        $existing = $groups[$service]['methods'][$method];
        if ($existing['method'] !== $request['method'] || $existing['url'] !== $request['url']) {
            fail(sprintf('Method collision for %s', $key));
        }
    } else {
        $groups[$service]['group'] = $group;
        $groups[$service]['methods'][$method] = $request;
    }

    $request['implementation'] = 'Fleetbase\\Sdk\\Services\\' . $service . '::' . $method;
    $request['tests'] = ['tests/Contract/EndpointContractTest.php::testEveryEndpointContract'];
    $request['status'] = 'complete';
    $request['exception'] = null;
    $requests[$index] = $request;
}

ksort($groups);
$concernsDirectory = $sourceRoot . '/Concerns';
$resourcesDirectory = dirname($sourceRoot) . '/Resources';
ensureDirectory($concernsDirectory);
ensureDirectory($resourcesDirectory);

$serviceMap = [];
foreach ($groups as $service => $definition) {
    $group = $definition['group'];
    $trait = $service . 'Endpoints';
    $methods = $definition['methods'];
    ksort($methods);
    $traitCode = renderTrait($trait, $methods);
    writeGenerated($concernsDirectory . '/' . $trait . '.php', $traitCode);

    if ($service !== 'OrderService') {
        $resource = substr($service, 0, -strlen('Service'));
        $namespace = namespaceFromRequest(reset($methods));
        writeGenerated($sourceRoot . '/' . $service . '.php', renderService($service, $trait, $resource, $namespace));
    }

    $resource = substr($service, 0, -strlen('Service'));
    $resourcePath = $resourcesDirectory . '/' . $resource . '.php';
    if (!is_file($resourcePath)) {
        writeGenerated($resourcePath, renderResource($resource));
    }

    $serviceMap[$group] = [
        'service' => 'Fleetbase\\Sdk\\Services\\' . $service,
        'trait' => 'Fleetbase\\Sdk\\Services\\Concerns\\' . $trait,
        'requests' => count($methods),
    ];
}

$manifest['requests'] = $requests;
$manifest['summary']['mapped'] = count($requests);
$manifest['summary']['exceptions'] = 0;
$manifest['summary']['unmapped'] = 0;
writeJson($manifestPath, $manifest);
writeJson('contracts/service-map.json', [
    'schema_version' => 1,
    'generated_from' => $manifest['source'],
    'groups' => $serviceMap,
]);
writeGenerated($testPath, renderContractTest() . "\n");

printf("Generated %d service groups and mapped %d requests.\n", count($groups), count($requests));

/** @return array<string, mixed> */
function readManifest(string $path): array
{
    $contents = file_get_contents($path);
    if (!is_string($contents)) {
        fail(sprintf('Unable to read manifest: %s', $path));
    }
    $data = json_decode($contents, true);
    if (!is_array($data)) {
        fail(sprintf('Invalid manifest JSON: %s', $path));
    }
    return $data;
}

/** @param array<mixed, mixed> $data */
function requiredString(array $data, string $key): string
{
    $value = $data[$key] ?? null;
    if (!is_string($value) || $value === '') {
        fail(sprintf('Missing manifest field: %s', $key));
    }
    return $value;
}

function serviceName(string $group): string
{
    $singular = Doctrine\Inflector\InflectorFactory::create()->build()->singularize($group);
    return Doctrine\Inflector\InflectorFactory::create()->build()->classify($singular) . 'Service';
}

function methodName(string $name): string
{
    $name = preg_replace('/\b(?:a|an|the)\b/i', ' ', $name) ?? $name;
    $name = preg_replace('/[^A-Za-z0-9]+/', ' ', $name) ?? $name;
    $classified = Doctrine\Inflector\InflectorFactory::create()->build()->classify(strtolower(trim($name)));
    return lcfirst($classified);
}

/** @param array<string, mixed> $request @return array<string, mixed> */
function signatureForRequest(array $request): array
{
    $pathParameters = pathParameters(requiredString($request, 'url'));
    $fixture = is_array($request['request_fixture'] ?? null) ? $request['request_fixture'] : [];
    $bodyType = is_string($fixture['body_type'] ?? null) ? $fixture['body_type'] : null;
    $body = $fixture['body'] ?? null;
    $query = $fixture['query'] ?? [];
    $contractPayload = 'none';
    if ($bodyType === 'formdata' && is_array($body)) {
        $contractPayload = 'multipart';
    } elseif ($bodyType === 'text' && is_string($body) && $body !== '') {
        $contractPayload = 'raw';
    } elseif ($bodyType === 'json' && is_array($body)) {
        $contractPayload = 'json';
    } elseif (is_array($query) && $query !== []) {
        $contractPayload = 'query';
    }

    $method = strtoupper(requiredString($request, 'method'));
    $requestData = in_array($method, ['GET', 'HEAD'], true) ? 'query' : 'body';
    if ($bodyType === 'formdata') {
        $requestData = 'multipart';
    }

    return [
        'path_parameters' => $pathParameters,
        'request_data' => $requestData,
        'contract_payload' => $contractPayload,
        'legacy_envelope' => true,
    ];
}

/** @return array<int, string> */
function pathParameters(string $url): array
{
    preg_match_all('/(?:\{\{([^}]+)\}\}|:([A-Za-z][A-Za-z0-9_-]*))/', $url, $matches, PREG_SET_ORDER);
    $parameters = [];
    foreach ($matches as $match) {
        $name = ($match[1] ?? '') !== '' ? $match[1] : ($match[2] ?? '');
        if ($name !== '' && !in_array($name, ['base_url', 'namespace'], true) && !in_array($name, $parameters, true)) {
            $parameters[] = $name;
        }
    }
    return $parameters;
}

/** @param array<string, array<mixed, mixed>> $methods */
function renderTrait(string $trait, array $methods): string
{
    $code = generatedHeader('Fleetbase\\Sdk\\Services\\Concerns');
    $code .= "trait {$trait}\n{\n";
    foreach ($methods as $method => $request) {
        $verb = var_export(requiredString($request, 'method'), true);
        $url = var_export(requiredString($request, 'url'), true);
        $description = str_replace('*/', '* /', requiredString($request, 'name'));
        $signature = is_array($request['sdk_signature'] ?? null) ? $request['sdk_signature'] : signatureForRequest($request);
        $pathParameters = is_array($signature['path_parameters'] ?? null) ? $signature['path_parameters'] : [];
        $requestData = var_export(is_string($signature['request_data'] ?? null) ? $signature['request_data'] : 'body', true);
        $code .= "    /**\n";
        $code .= "     * {$description}.\n";
        $code .= "     *\n";
        if ($pathParameters !== []) {
            $code .= "     * @param scalar|\\Fleetbase\\Sdk\\Resource|array<string, mixed> \$parameters First path value, or the legacy endpoint envelope.\n";
            $code .= "     * @param mixed \$options Request data, a second path value, or legacy request options.\n";
            if (count($pathParameters) === 1) {
                $code .= "     * @param array<string, mixed> \$requestOptions\n";
            } else {
                $code .= "     * @param array<string, mixed> \$data\n";
                $code .= "     * @param array<string, mixed> \$requestOptions\n";
            }
        } else {
            $code .= "     * @param array<string, mixed> \$parameters\n";
            $code .= "     * @param array<string, mixed> \$options\n";
        }
        $code .= "     * @return mixed\n";
        $code .= "     */\n";
        if (count($pathParameters) === 1) {
            $code .= "    public function {$method}(\$parameters = [], \$options = [], \$requestOptions = [])\n";
        } elseif (count($pathParameters) === 2) {
            $code .= "    public function {$method}(\$parameters = [], \$options = [], \$data = [], \$requestOptions = [])\n";
        } else {
            $code .= "    public function {$method}(array \$parameters = [], array \$options = [])\n";
        }
        $code .= "    {\n";
        $code .= '        return $this->endpointFromArguments(' . $verb . ', ' . $url . ', ' . exportedStringList($pathParameters) . ", {$requestData}, func_get_args());\n";
        $code .= "    }\n\n";
    }
    return rtrim($code) . "\n}\n";
}

/** @param array<int, string> $values */
function exportedStringList(array $values): string
{
    return '[' . implode(', ', array_map(static function (string $value): string {
        return var_export($value, true);
    }, $values)) . ']';
}

function renderService(string $service, string $trait, string $resource, string $namespace): string
{
    $code = generatedHeader('Fleetbase\\Sdk\\Services');
    $code .= "use Fleetbase\\Sdk\\EndpointService;\n";
    $code .= "use Fleetbase\\Sdk\\HttpClient;\n";
    $code .= "use Fleetbase\\Sdk\\Services\\Concerns\\{$trait};\n\n";
    $code .= "class {$service} extends EndpointService\n{\n";
    $code .= "    use {$trait};\n\n";
    $code .= "    /** @param array<string, mixed> \$options */\n";
    $code .= "    public function __construct(HttpClient \$client, array \$options = [])\n";
    $code .= "    {\n";
    $code .= '        parent::__construct(' . var_export($resource, true) . ", \$client, array_merge(['namespace' => " . var_export($namespace, true) . "], \$options));\n";
    $code .= "    }\n";
    $code .= "}\n";
    return $code;
}

function renderResource(string $resource): string
{
    $code = generatedHeader('Fleetbase\\Sdk\\Resources');
    $code .= "use Fleetbase\\Sdk\\Resource;\n\n";
    $code .= "class {$resource} extends Resource\n{\n}\n";
    return $code;
}

/** @param array<mixed, mixed>|false $request */
function namespaceFromRequest($request): string
{
    if (!is_array($request)) {
        fail('Unable to determine service namespace.');
    }
    $url = requiredString($request, 'url');
    $path = preg_replace('#^\{\{base_url\}\}/\{\{namespace\}\}/?#i', '', $url) ?? $url;
    $path = explode('?', $path, 2)[0];
    $segment = explode('/', trim($path, '/'))[0] ?? '';
    if ($segment === '' || strpos($segment, '{{') !== false) {
        fail(sprintf('Unable to determine namespace from URL: %s', $url));
    }
    return $segment;
}

function generatedHeader(string $namespace): string
{
    return "<?php\n\n/**\n * Generated from the locked Fleetbase Postman contract.\n * Do not edit by hand; run tools/generate-endpoint-services.php.\n *\n * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later\n */\n\ndeclare(strict_types=1);\n\nnamespace {$namespace};\n\n";
}

function renderContractTest(): string
{
    return <<<'PHP'
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
                    [$serviceClass, $method, $httpMethod, $urlTemplate, $pathValues, $requestFixture] = $case;
                    [$legacyArguments, $ergonomicArguments, $query, $expectedBody, $expectedMultipart] =
                        self::invocations($pathValues, $requestFixture);
                    $client = $this->mockHttpClient([
                        new Response(200, ['Content-Type' => 'application/json'], '{}'),
                        new Response(200, ['Content-Type' => 'application/json'], '{}'),
                    ]);
                    $reflection = new ReflectionClass($serviceClass);
                    $service = $reflection->newInstance($client);
                    $service->{$method}(...$legacyArguments);
                    $service->{$method}(...$ergonomicArguments);

                    $legacyRequest = self::requestFrom($this->history, 0);
                    $ergonomicRequest = self::requestFrom($this->history, 1);
                    self::assertSame($httpMethod, $legacyRequest->getMethod());
                    self::assertSame($httpMethod, $ergonomicRequest->getMethod());
                    self::assertSame('legacy', $legacyRequest->getHeaderLine('X-SDK-Invocation'));
                    self::assertSame('ergonomic', $ergonomicRequest->getHeaderLine('X-SDK-Invocation'));

                    $expected = preg_replace('#^\{\{base_url\}\}/\{\{namespace\}\}/?#i', '', $urlTemplate);
                    self::assertIsString($expected);
                    foreach ($pathValues as $name => $value) {
                        $expected = str_replace('{{' . $name . '}}', rawurlencode((string) $value), $expected);
                        $expected = preg_replace('/:' . preg_quote($name, '/') . '(?![A-Za-z0-9_-])/', rawurlencode((string) $value), $expected);
                        self::assertIsString($expected);
                    }
                    if ($query !== []) {
                        $expected .= (strpos($expected, '?') === false ? '?' : '&')
                            . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
                    }
                    $expectedUri = '/v1/' . ltrim($expected, '/');
                    self::assertSame($expectedUri, self::pathAndQuery($legacyRequest));
                    self::assertSame($expectedUri, self::pathAndQuery($ergonomicRequest));
                    if (is_array($expectedBody)) {
                        self::assertSame($expectedBody, json_decode((string) $legacyRequest->getBody(), true, 512, JSON_THROW_ON_ERROR));
                        self::assertSame($expectedBody, json_decode((string) $ergonomicRequest->getBody(), true, 512, JSON_THROW_ON_ERROR));
                    }
                    foreach ($expectedMultipart as $name => $contents) {
                        self::assertStringContainsString('name="' . $name . '"', (string) $legacyRequest->getBody());
                        self::assertStringContainsString('name="' . $name . '"', (string) $ergonomicRequest->getBody());
                        self::assertStringContainsString($contents, (string) $legacyRequest->getBody());
                        self::assertStringContainsString($contents, (string) $ergonomicRequest->getBody());
                    }
                }
            }

            /**
             * @param array<string, scalar> $pathValues
             * @param array<string, mixed> $requestFixture
             * @return array{array<int, mixed>, array<int, mixed>, array<mixed>, array<mixed>|null, array<string, string>}
             */
            private static function invocations(array $pathValues, array $requestFixture): array
            {
                $query = self::normalizeFixture($requestFixture['query'] ?? []);
                $query = is_array($query) ? $query : [];
                $bodyType = $requestFixture['body_type'] ?? null;
                $body = self::normalizeFixture($requestFixture['body'] ?? null);
                $data = $query;
                $legacyParameters = $pathValues;
                $legacyOptions = ['headers' => ['X-SDK-Invocation' => 'legacy']];
                $expectedBody = null;
                $expectedMultipart = [];
                if ($bodyType === 'json' && is_array($body)) {
                    $data = $body;
                    $legacyParameters['body'] = $body;
                    $expectedBody = $body;
                } elseif ($bodyType === 'text' && is_string($body)) {
                    $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
                    if (!is_array($decoded)) {
                        throw new \RuntimeException('Raw endpoint fixture must decode to an object.');
                    }
                    $data = $decoded;
                    $legacyOptions['body'] = $body;
                    $expectedBody = $decoded;
                } elseif ($bodyType === 'formdata' && is_array($body)) {
                    $data = [];
                    foreach ($body as $part) {
                        if (!is_array($part) || !is_string($part['key'] ?? null)) {
                            continue;
                        }
                        $normalizedValue = self::normalizeFixture($part['value'] ?? '');
                        $contents = ($part['type'] ?? null) === 'file'
                            ? 'fixture-file-content'
                            : (is_scalar($normalizedValue) ? (string) $normalizedValue : '');
                        $data[] = ['name' => $part['key'], 'contents' => $contents];
                        $legacyOptions['multipart'][] = ['name' => $part['key'], 'contents' => $contents];
                        $expectedMultipart[$part['key']] = $contents;
                    }
                } elseif ($query !== []) {
                    $legacyOptions['query'] = $query;
                }

                $legacyArguments = [$legacyParameters, $legacyOptions];
                $ergonomicArguments = array_values($pathValues);
                $ergonomicArguments[] = $data;
                $ergonomicArguments[] = ['headers' => ['X-SDK-Invocation' => 'ergonomic']];
                return [$legacyArguments, $ergonomicArguments, $query, $expectedBody, $expectedMultipart];
            }

            /** @param array<mixed> $history */
            private static function requestFrom(array $history, int $index): RequestInterface
            {
                $transaction = $history[$index] ?? null;
                if (!is_array($transaction) || !($transaction['request'] ?? null) instanceof RequestInterface) {
                    throw new \RuntimeException('Endpoint invocation did not issue an HTTP request.');
                }
                return $transaction['request'];
            }

            private static function pathAndQuery(RequestInterface $request): string
            {
                return $request->getUri()->getPath()
                    . ($request->getUri()->getQuery() !== '' ? '?' . $request->getUri()->getQuery() : '');
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
                        if (!in_array($name, ['base_url', 'namespace'], true) && !array_key_exists($name, $parameters)) {
                            $parameters[$name] = $name . '/fixture';
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
        PHP;
}

/** @param array<string, mixed> $data */
function writeJson(string $path, array $data): void
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (!is_string($json)) {
        fail(sprintf('Unable to encode JSON: %s', $path));
    }
    writeGenerated($path, $json . "\n");
}

function writeGenerated(string $path, string $contents): void
{
    ensureDirectory(dirname($path));
    if (file_put_contents($path, $contents) === false) {
        fail(sprintf('Unable to write generated file: %s', $path));
    }
}

function ensureDirectory(string $path): void
{
    if (!is_dir($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
        fail(sprintf('Unable to create directory: %s', $path));
    }
}

function fail(string $message): void
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}
