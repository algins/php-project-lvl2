<?php

namespace Differ\Formatters\Plain;

use Exception;

use const Differ\Differ\STATE_ADDED;
use const Differ\Differ\STATE_CHANGED;
use const Differ\Differ\STATE_REMOVED;
use const Differ\Differ\STATE_UNCHANGED;
use const Differ\Differ\TYPE_LEAF;

function render(array $tree): string
{
    return iter($tree);
}

function iter(array $tree, array $parentKeys = []): string
{
    $parts = array_map(function ($node) use ($parentKeys) {
        $keys = [...$parentKeys, $node['key']];
        $property = implode('.', $keys);

        if ($node['type'] === TYPE_LEAF) {
            switch ($node['state']) {
                case STATE_ADDED:
                    $stringifiedValue = stringify($node['value']);
                    return "Property '{$property}' was added with value: {$stringifiedValue}";
                case STATE_REMOVED:
                    return "Property '{$property}' was removed";
                case STATE_CHANGED:
                    $stringifiedValue1 = stringify($node['value1']);
                    $stringifiedValue2 = stringify($node['value2']);
                    return "Property '{$property}' was updated. From {$stringifiedValue1} to {$stringifiedValue2}";
                case STATE_UNCHANGED:
                    return;
                default:
                    throw new Exception('Invalid state!');
            }
        }

        return iter($node, $keys);
    }, $tree['children']);

    return implode("\n", array_filter($parts));
}

/** @param mixed $value */
function stringify($value): string
{
    switch (gettype($value)) {
        case 'boolean':
            return stringifyBool($value);
        case 'NULL':
            return 'null';
        case 'integer':
            return (string) $value;
        case 'array':
        case 'object':
            return '[complex value]';
        default:
            return "'{$value}'";
    }
}

function stringifyBool(bool $value): string
{
    return $value ? 'true' : 'false';
}
