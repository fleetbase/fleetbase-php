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

    $requests[$index]['implementation'] = 'Fleetbase\\Sdk\\Services\\' . $service . '::' . $method;
    $requests[$index]['tests'] = ['tests/Contract/EndpointContractTest.php::testEveryEndpointContract'];
    $requests[$index]['status'] = 'complete';
    $requests[$index]['exception'] = null;
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

/** @param array<string, array<mixed, mixed>> $methods */
function renderTrait(string $trait, array $methods): string
{
    $code = generatedHeader('Fleetbase\\Sdk\\Services\\Concerns');
    $code .= "trait {$trait}\n{\n";
    foreach ($methods as $method => $request) {
        $verb = var_export(requiredString($request, 'method'), true);
        $url = var_export(requiredString($request, 'url'), true);
        $description = str_replace('*/', '* /', requiredString($request, 'name'));
        $code .= "    /**\n";
        $code .= "     * {$description}.\n";
        $code .= "     *\n";
        $code .= "     * @param array<string, mixed> \$parameters\n";
        $code .= "     * @param array<string, mixed> \$options\n";
        $code .= "     * @return mixed\n";
        $code .= "     */\n";
        $code .= "    public function {$method}(array \$parameters = [], array \$options = [])\n";
        $code .= "    {\n";
        $code .= "        return \$this->endpoint({$verb}, {$url}, \$parameters, \$options);\n";
        $code .= "    }\n\n";
    }
    return rtrim($code) . "\n}\n";
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
