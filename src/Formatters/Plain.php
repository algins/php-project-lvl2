<?php

namespace Differ\Formatters\Plain;

use const Differ\Differ\STATE_ADDED;
use const Differ\Differ\STATE_CHANGED;
use const Differ\Differ\STATE_REMOVED;
use const Differ\Differ\STATE_UNCHANGED;
use const Differ\Differ\TYPE_FLAT;
use const Differ\Differ\TYPE_NESTED;

function format(array $diff): string
{
    $lines = buildLines($diff);

    return implode("\n", $lines);
}

function buildLines(array $diff, array $propertyPathParts = []): array
{
    return array_reduce($diff, function ($acc, $item) use ($propertyPathParts) {
        ['key' => $key, 'type' => $type] = $item;
        $newPropertyPathParts = [...$propertyPathParts, $key];

        if ($type === TYPE_NESTED) {
            ['children' => $nestedDiff] = $item;
            return [...$acc, ...buildLines($nestedDiff, $newPropertyPathParts)];
        }

        ['state' => $state, 'values' => $values] = $item;

        if ($state === STATE_UNCHANGED) {
            return $acc;
        }

        $propertyPath = implode('.', $newPropertyPathParts);

        return [...$acc, buildLine($state, $propertyPath, $values)];
    }, []);
}

function buildLine(string $state, string $propertyPath, array $values): string
{
    $states = [
        STATE_ADDED => function (string $propertyPath, array $values): string {
            ['second' => $currentValue] = $values;
            return sprintf(
                "Property '%s' was added with value: %s",
                $propertyPath,
                prepareValue($currentValue)
            );
        },
        STATE_REMOVED => function (string $propertyPath): string {
            return sprintf("Property '%s' was removed", $propertyPath);
        },
        STATE_CHANGED => function (string $propertyPath, array $values): string {
            ['second' => $currentValue, 'first' => $previousValue] = $values;
            return sprintf(
                "Property '%s' was updated. From %s to %s",
                $propertyPath,
                prepareValue($previousValue),
                prepareValue($currentValue)
            );
        },
    ];

    return $states[$state]($propertyPath, $values);
}

/** @param mixed $value */
function prepareValue($value): string
{
    switch (true) {
        case is_bool($value):
            $preparedValue = prepareBoolValue($value);
            break;
        case is_null($value):
            $preparedValue = 'null';
            break;
        case is_int($value):
            $preparedValue = (string) $value;
            break;
        case is_array($value) || is_object($value):
            $preparedValue = '[complex value]';
            break;
        default:
            $preparedValue = "'{$value}'";
    }

    return $preparedValue;
}

function prepareBoolValue(bool $value): string
{
    return $value ? 'true' : 'false';
}
