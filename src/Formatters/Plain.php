<?php

namespace Differ\Formatters\Plain;

use const Differ\Differ\STATE_ADDED;
use const Differ\Differ\STATE_CHANGED;
use const Differ\Differ\STATE_REMOVED;
use const Differ\Differ\STATE_UNCHANGED;
use const Differ\Differ\TYPE_INTERNAL;

function render(array $tree): string
{
    $lines = buildLines($tree);

    return implode("\n", $lines);
}

function buildLines(array $tree, array $propertyPathParts = []): array
{
    $nodes = $tree['children'];

    return array_reduce($nodes, function ($acc, $node) use ($propertyPathParts) {
        ['key' => $key, 'type' => $type] = $node;
        $newPropertyPathParts = [...$propertyPathParts, $key];

        if ($type === TYPE_INTERNAL) {
            return [...$acc, ...buildLines($node, $newPropertyPathParts)];
        }

        $state = $node['state'];
        $values = array_key_exists('value', $node)
            ? ['value' => $node['value']]
            : ['value1' => $node['value1'], 'value2' => $node['value2']];

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
            ['value' => $value] = $values;
            return sprintf(
                "Property '%s' was added with value: %s",
                $propertyPath,
                prepareValue($value)
            );
        },
        STATE_REMOVED => function (string $propertyPath): string {
            return sprintf("Property '%s' was removed", $propertyPath);
        },
        STATE_CHANGED => function (string $propertyPath, array $values): string {
            ['value2' => $value2, 'value1' => $value1] = $values;
            return sprintf(
                "Property '%s' was updated. From %s to %s",
                $propertyPath,
                prepareValue($value1),
                prepareValue($value2)
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
