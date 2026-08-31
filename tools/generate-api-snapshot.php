<?php

declare(strict_types=1);

/**
 * Generate a deterministic snapshot of the SDK's public PHP API.
 *
 * Usage: php tools/generate-api-snapshot.php --autoload=/path/vendor/autoload.php
 *        --label=1.0.2 --output=contracts/public-api-1.0.2.json
 */

$options = getopt('', ['autoload:', 'label:', 'output:']);

foreach (['autoload', 'label', 'output'] as $required) {
    if (!isset($options[$required]) || !is_string($options[$required]) || $options[$required] === '') {
        fwrite(STDERR, sprintf("Missing required --%s option.\n", $required));
        exit(2);
    }
}

$autoload = realpath($options['autoload']);
if ($autoload === false || !is_file($autoload)) {
    fwrite(STDERR, sprintf("Autoloader not found: %s\n", $options['autoload']));
    exit(2);
}

require $autoload;

$sourceRoot = dirname(dirname($autoload)) . '/src';
$files = glob($sourceRoot . '/*.php') ?: [];
$resourceFiles = glob($sourceRoot . '/Resources/*.php') ?: [];
$serviceFiles = glob($sourceRoot . '/Services/*.php') ?: [];

foreach (array_merge($files, $resourceFiles, $serviceFiles) as $file) {
    require_once $file;
}

$classes = array_values(array_filter(
    get_declared_classes(),
    static function (string $class): bool {
        return strpos($class, 'Fleetbase\\Sdk\\') === 0;
    }
));
sort($classes);

$snapshot = [
    'schema_version' => 1,
    'release' => $options['label'],
    'namespace' => 'Fleetbase\\Sdk',
    'classes' => [],
    'runtime_properties' => [],
];

foreach ($classes as $class) {
    $reflection = new ReflectionClass($class);
    $constants = [];
    foreach ($reflection->getReflectionConstants() as $constant) {
        if ($constant->isPublic()) {
            $constants[$constant->getName()] = $constant->getValue();
        }
    }
    ksort($constants);

    $properties = [];
    foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED) as $property) {
        if ($property->getDeclaringClass()->getName() !== $class) {
            continue;
        }

        $properties[] = [
            'name' => $property->getName(),
            'visibility' => $property->isPublic() ? 'public' : 'protected',
            'static' => $property->isStatic(),
            'type' => method_exists($property, 'getType') ? formatType($property->getType()) : null,
        ];
    }
    usort($properties, static function (array $left, array $right): int {
        return strcmp($left['name'], $right['name']);
    });

    $methods = [];
    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED) as $method) {
        if ($method->getDeclaringClass()->getName() !== $class) {
            continue;
        }

        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            $item = [
                'name' => $parameter->getName(),
                'type' => formatType($parameter->getType()),
                'by_reference' => $parameter->isPassedByReference(),
                'variadic' => $parameter->isVariadic(),
                'optional' => $parameter->isOptional(),
            ];

            if ($parameter->isDefaultValueAvailable()) {
                $item['default'] = normalizeValue($parameter->getDefaultValue());
            }

            $parameters[] = $item;
        }

        $methods[] = [
            'name' => $method->getName(),
            'visibility' => $method->isPublic() ? 'public' : 'protected',
            'static' => $method->isStatic(),
            'return_type' => formatType($method->getReturnType()),
            'parameters' => $parameters,
        ];
    }
    usort($methods, static function (array $left, array $right): int {
        return strcmp($left['name'], $right['name']);
    });

    $snapshot['classes'][$class] = [
        'final' => $reflection->isFinal(),
        'parent' => ($parent = $reflection->getParentClass()) ? $parent->getName() : null,
        'interfaces' => array_values($reflection->getInterfaceNames()),
        'constants' => normalizeValue($constants),
        'properties' => $properties,
        'methods' => $methods,
    ];
}

if (class_exists('Fleetbase\\Sdk\\Fleetbase')) {
    set_error_handler(static function (int $severity): bool {
        return $severity === E_DEPRECATED;
    });
    try {
        $fleetbase = new Fleetbase\Sdk\Fleetbase('contract-snapshot-placeholder-key');
        $runtimeProperties = array_keys(get_object_vars($fleetbase));
        sort($runtimeProperties);
        $snapshot['runtime_properties']['Fleetbase\\Sdk\\Fleetbase'] = $runtimeProperties;
    } finally {
        restore_error_handler();
    }
}

ksort($snapshot['classes']);
ksort($snapshot['runtime_properties']);

$json = json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (!is_string($json)) {
    fwrite(STDERR, "Unable to encode API snapshot.\n");
    exit(1);
}

$outputDirectory = dirname($options['output']);
if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0777, true) && !is_dir($outputDirectory)) {
    fwrite(STDERR, sprintf("Unable to create output directory: %s\n", $outputDirectory));
    exit(1);
}

if (file_put_contents($options['output'], $json . "\n") === false) {
    fwrite(STDERR, sprintf("Unable to write snapshot: %s\n", $options['output']));
    exit(1);
}

function formatType(?ReflectionType $type): ?string
{
    if ($type === null) {
        return null;
    }

    if ($type instanceof ReflectionNamedType) {
        return ($type->allowsNull() && $type->getName() !== 'mixed' ? '?' : '') . $type->getName();
    }

    return (string) $type;
}

/**
 * @param mixed $value
 * @return mixed
 */
function normalizeValue($value)
{
    if (is_array($value)) {
        foreach ($value as $key => $item) {
            $value[$key] = normalizeValue($item);
        }
    }

    if (is_object($value)) {
        return ['class' => get_class($value)];
    }

    if (is_resource($value)) {
        return ['resource' => get_resource_type($value)];
    }

    return $value;
}
