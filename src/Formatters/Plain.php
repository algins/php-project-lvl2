<?php

namespace Differ\Formatters\Plain;

use Exception;

use const Differ\Differ\TYPE_ADDED;
use const Differ\Differ\TYPE_CHANGED;
use const Differ\Differ\TYPE_NESTED;
use const Differ\Differ\TYPE_REMOVED;
use const Differ\Differ\TYPE_ROOT;
use const Differ\Differ\TYPE_UNCHANGED;

function render(array $tree): string
{
    return iter($tree);
}

function iter(array $node, array $parentKeys = []): ?string
{
    $keys = array_key_exists('key', $node) ? [...$parentKeys, $node['key']] : $parentKeys;
    $property = implode('.', $keys);

    switch ($node['type']) {
        case TYPE_ADDED:
            $value = stringify($node['value']);
            return "Property '{$property}' was added with value: {$value}";

        case TYPE_REMOVED:
            return "Property '{$property}' was removed";

        case TYPE_CHANGED:
            $value1 = stringify($node['value1']);
            $value2 = stringify($node['value2']);
            return "Property '{$property}' was updated. From {$value1} to {$value2}";

        case TYPE_UNCHANGED:
            return null;

        case TYPE_NESTED:
        case TYPE_ROOT:
            $children = array_map(fn($child) => iter($child, $keys), $node['children']);
            return implode("\n", array_filter($children));

        default:
            throw new Exception("Unknown type: {$node['type']}");
    }
}

/** @param mixed $value */
function stringify($value): string
{
    switch (true) {
        case is_bool($value):
            return stringifyBool($value);
        case is_null($value):
            return 'null';
        case is_int($value):
            return (string) $value;
        case is_object($value):
        case is_array($value):
            return '[complex value]';
        default:
            return "'{$value}'";
    }
}

function stringifyBool(bool $value): string
{
    return $value ? 'true' : 'false';
}
