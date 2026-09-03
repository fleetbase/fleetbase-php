<?php

declare(strict_types=1);

/**
 * Check that a generated API snapshot preserves a baseline snapshot.
 *
 * Usage: php tools/check-api-compatibility.php --baseline=baseline.json --current=current.json
 */

$options = getopt('', ['baseline:', 'current:']);
foreach (['baseline', 'current'] as $required) {
    if (!isset($options[$required]) || !is_string($options[$required]) || $options[$required] === '') {
        fwrite(STDERR, sprintf("Missing required --%s option.\n", $required));
        exit(2);
    }
}

$baseline = readSnapshot($options['baseline']);
$current = readSnapshot($options['current']);
$errors = [];

foreach ($baseline['classes'] as $className => $baselineClass) {
    if (!isset($current['classes'][$className])) {
        $errors[] = sprintf('Removed class %s', $className);
        continue;
    }

    $currentClass = $current['classes'][$className];
    if (($baselineClass['final'] ?? false) === false && ($currentClass['final'] ?? false) === true) {
        $errors[] = sprintf('Class %s became final', $className);
    }
    if (($baselineClass['parent'] ?? null) !== ($currentClass['parent'] ?? null)) {
        $errors[] = sprintf('Class %s changed parent', $className);
    }

    foreach ($baselineClass['interfaces'] ?? [] as $interface) {
        if (!in_array($interface, $currentClass['interfaces'] ?? [], true)) {
            $errors[] = sprintf('Class %s removed interface %s', $className, $interface);
        }
    }

    foreach ($baselineClass['constants'] ?? [] as $constant => $value) {
        if (!array_key_exists($constant, $currentClass['constants'] ?? [])) {
            $errors[] = sprintf('Class %s removed constant %s', $className, $constant);
        } elseif (($currentClass['constants'][$constant] ?? null) !== $value) {
            $errors[] = sprintf('Class %s changed constant %s', $className, $constant);
        }
    }

    compareMembers($errors, $className, 'property', $baselineClass['properties'] ?? [], $currentClass['properties'] ?? []);
    compareMethods($errors, $className, $baselineClass['methods'] ?? [], $currentClass['methods'] ?? []);
}

foreach ($baseline['runtime_properties'] ?? [] as $className => $properties) {
    $currentProperties = $current['runtime_properties'][$className] ?? [];
    foreach ($properties as $property) {
        if (!in_array($property, $currentProperties, true)) {
            $errors[] = sprintf('Class %s removed runtime property %s', $className, $property);
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Backwards-compatibility check failed:\n- " . implode("\n- ", $errors) . "\n");
    exit(1);
}

printf(
    "API compatibility passed: %s is compatible with %s.\n",
    $current['release'] ?? $options['current'],
    $baseline['release'] ?? $options['baseline']
);

/**
 * @return array<string, mixed>
 */
function readSnapshot(string $path): array
{
    $contents = file_get_contents($path);
    if (!is_string($contents)) {
        fwrite(STDERR, sprintf("Unable to read snapshot: %s\n", $path));
        exit(2);
    }

    $snapshot = json_decode($contents, true);
    if (!is_array($snapshot) || !isset($snapshot['classes'])) {
        fwrite(STDERR, sprintf("Invalid snapshot: %s\n", $path));
        exit(2);
    }

    return $snapshot;
}

/**
 * @param array<int, string> $errors
 * @param array<int, array<string, mixed>> $baselineMembers
 * @param array<int, array<string, mixed>> $currentMembers
 */
function compareMembers(array &$errors, string $className, string $kind, array $baselineMembers, array $currentMembers): void
{
    $currentByName = indexByName($currentMembers);
    foreach ($baselineMembers as $baselineMember) {
        $name = (string) $baselineMember['name'];
        if (!isset($currentByName[$name])) {
            $errors[] = sprintf('Class %s removed %s %s', $className, $kind, $name);
            continue;
        }

        $currentMember = $currentByName[$name];
        if (($baselineMember['visibility'] ?? 'public') === 'public' && ($currentMember['visibility'] ?? 'public') !== 'public') {
            $errors[] = sprintf('Class %s narrowed visibility of %s %s', $className, $kind, $name);
        }
        if (($baselineMember['static'] ?? false) !== ($currentMember['static'] ?? false)) {
            $errors[] = sprintf('Class %s changed static flag of %s %s', $className, $kind, $name);
        }
        if (($baselineMember['type'] ?? null) !== ($currentMember['type'] ?? null)) {
            $errors[] = sprintf('Class %s changed type of %s %s', $className, $kind, $name);
        }
    }
}

/**
 * @param array<int, string> $errors
 * @param array<int, array<string, mixed>> $baselineMethods
 * @param array<int, array<string, mixed>> $currentMethods
 */
function compareMethods(array &$errors, string $className, array $baselineMethods, array $currentMethods): void
{
    $currentByName = indexByName($currentMethods);
    foreach ($baselineMethods as $baselineMethod) {
        $name = (string) $baselineMethod['name'];
        if (!isset($currentByName[$name])) {
            $errors[] = sprintf('Class %s removed method %s', $className, $name);
            continue;
        }

        $currentMethod = $currentByName[$name];
        if (($baselineMethod['visibility'] ?? 'public') === 'public' && ($currentMethod['visibility'] ?? 'public') !== 'public') {
            $errors[] = sprintf('Class %s narrowed visibility of method %s', $className, $name);
        }
        if (($baselineMethod['static'] ?? false) !== ($currentMethod['static'] ?? false)) {
            $errors[] = sprintf('Class %s changed static flag of method %s', $className, $name);
        }
        if (($baselineMethod['return_type'] ?? null) !== ($currentMethod['return_type'] ?? null)) {
            $errors[] = sprintf('Class %s changed return type of method %s', $className, $name);
        }

        $baselineParameters = $baselineMethod['parameters'] ?? [];
        $currentParameters = $currentMethod['parameters'] ?? [];
        if (count($currentParameters) < count($baselineParameters)) {
            $errors[] = sprintf('Class %s removed parameters from method %s', $className, $name);
            continue;
        }

        foreach ($baselineParameters as $index => $baselineParameter) {
            $currentParameter = $currentParameters[$index];
            if (($baselineParameter['name'] ?? null) !== ($currentParameter['name'] ?? null)
                && !hasCompatibleNamedParameter($baselineParameter, $currentParameters)) {
                $errors[] = sprintf('Class %s removed named parameter %s from method %s', $className, $baselineParameter['name'], $name);
            }
            foreach (['by_reference', 'variadic'] as $field) {
                if (($baselineParameter[$field] ?? null) !== ($currentParameter[$field] ?? null)) {
                    $errors[] = sprintf('Class %s changed parameter %d %s of method %s', $className, $index + 1, $field, $name);
                }
            }
            if (!isCompatibleParameterType($baselineParameter['type'] ?? null, $currentParameter['type'] ?? null)) {
                $errors[] = sprintf('Class %s narrowed parameter %d type of method %s', $className, $index + 1, $name);
            }
            if (($baselineParameter['optional'] ?? false) !== ($currentParameter['optional'] ?? false)) {
                $errors[] = sprintf('Class %s changed optionality of parameter %d on method %s', $className, $index + 1, $name);
            }
            if (array_key_exists('default', $baselineParameter)
                && (!array_key_exists('default', $currentParameter) || $baselineParameter['default'] !== $currentParameter['default'])) {
                $errors[] = sprintf('Class %s changed default of parameter %d on method %s', $className, $index + 1, $name);
            }
        }

        foreach (array_slice($currentParameters, count($baselineParameters)) as $newParameter) {
            if (($newParameter['optional'] ?? false) !== true && ($newParameter['variadic'] ?? false) !== true) {
                $errors[] = sprintf('Class %s added required parameter %s to method %s', $className, $newParameter['name'], $name);
            }
        }
    }
}

/** @param mixed $baselineType @param mixed $currentType */
function isCompatibleParameterType($baselineType, $currentType): bool
{
    return $baselineType === $currentType || $currentType === null || $currentType === 'mixed';
}

/**
 * PHP 8 named calls remain compatible when an optional parameter moves to a
 * later optional position with the same name and declaration.
 *
 * @param array<string, mixed> $baselineParameter
 * @param array<int, array<string, mixed>> $currentParameters
 */
function hasCompatibleNamedParameter(array $baselineParameter, array $currentParameters): bool
{
    foreach ($currentParameters as $currentParameter) {
        if (($currentParameter['name'] ?? null) !== ($baselineParameter['name'] ?? null)) {
            continue;
        }

        foreach (['by_reference', 'variadic', 'optional'] as $field) {
            if (($currentParameter[$field] ?? null) !== ($baselineParameter[$field] ?? null)) {
                return false;
            }
        }
        if (!isCompatibleParameterType($baselineParameter['type'] ?? null, $currentParameter['type'] ?? null)) {
            return false;
        }

        return !array_key_exists('default', $baselineParameter)
            || (array_key_exists('default', $currentParameter) && $currentParameter['default'] === $baselineParameter['default']);
    }

    return false;
}

/**
 * @param array<int, array<string, mixed>> $members
 * @return array<string, array<string, mixed>>
 */
function indexByName(array $members): array
{
    $indexed = [];
    foreach ($members as $member) {
        $indexed[(string) $member['name']] = $member;
    }

    return $indexed;
}
