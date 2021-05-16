<?php

namespace Differ\Formatters\Plain;

use function Funct\Collection\get;

use const Differ\Differ\TYPE_FLAT;
use const Differ\Differ\TYPE_NESTED;
use const Differ\Differ\STATE_ADDED;
use const Differ\Differ\STATE_REMOVED;
use const Differ\Differ\STATE_UPDATED;
use const Differ\Differ\STATE_UNCHANGED;

function format(array $diff): string
{
    $lines = buildLines($diff);

    return implode('', array_map(function ($line) {
        return "{$line}\n";
    }, $lines));
}

function buildLines(array $diff, array $propertyPathParts = []): array
{
    return array_reduce($diff, function ($acc, $item) use ($propertyPathParts) {
        ['key' => $key, 'type' => $type] = $item;
        $propertyPathParts[] = $key;

        if ($type === TYPE_NESTED) {
            ['diff' => $nestedDiff] = $item;
            return array_merge($acc, buildLines($nestedDiff, $propertyPathParts));
        }

        ['state' => $state, 'values' => $values] = $item;

        if ($state !== STATE_UNCHANGED) {
            $propertyPath = implode('.', $propertyPathParts);
            $acc[] = buildLine($state, $propertyPath, $values);
        }

        return $acc;
    }, []);
}

function buildLine(string $state, string $propertyPath, array $values): string
{
    $states = [
        STATE_ADDED => function (string $propertyPath, array $values): string {
            ['current' => $currentValue] = $values;
            return sprintf(
                "Property '%s' was added with value: %s",
                $propertyPath,
                prepareValue($currentValue)
            );
        },
        STATE_REMOVED => function (string $propertyPath): string {
            return sprintf(
                "Property '%s' was removed",
                $propertyPath
            );
        },
        STATE_UPDATED => function (string $propertyPath, array $values): string {
            ['current' => $currentValue, 'previous' => $previousValue] = $values;
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

function prepareValue($value): string
{
    switch (true) {
        case is_bool($value):
            $preparedValue = prepareBoolValue($value);
            break;
        case is_null($value):
            $preparedValue = 'null';
            break;
        case is_array($value) || is_object($value):
            $preparedValue = '[complex value]';
            break;
        default:
            $preparedValue = "'{$value}'";
            break;
    }

    return $preparedValue;
}

function prepareBoolValue(bool $value): string
{
    return $value ? 'true' : 'false';
}
