<?php

namespace Differ\Formatters\Stylish;

use Exception;

use const Differ\Differ\STATE_ADDED;
use const Differ\Differ\STATE_CHANGED;
use const Differ\Differ\STATE_REMOVED;
use const Differ\Differ\STATE_UNCHANGED;
use const Differ\Differ\TYPE_LEAF;

const SPACES_COUNT = 4;

function render(array $tree): string
{
    return iter($tree);
}

function iter(array $tree, int $depth = 1): string
{
    $indent = getIndent($depth);

    $parts = array_map(function ($node) use ($indent, $depth) {
        $key = $node['key'];

        if ($node['type'] === TYPE_LEAF) {
            switch ($node['state']) {
                case STATE_ADDED:
                    $stringifiedValue = stringify($node['value'], $depth);
                    return "{$indent}+ {$key}: {$stringifiedValue}";
                case STATE_REMOVED:
                    $stringifiedValue = stringify($node['value'], $depth);
                    return "{$indent}- {$key}: {$stringifiedValue}";
                case STATE_CHANGED:
                    $stringifiedValue1 = stringify($node['value1'], $depth);
                    $stringifiedValue2 = stringify($node['value2'], $depth);
                    return implode("\n", [
                        "{$indent}- {$key}: {$stringifiedValue1}",
                        "{$indent}+ {$key}: {$stringifiedValue2}",
                    ]);
                case STATE_UNCHANGED:
                    $stringifiedValue = stringify($node['value'], $depth);
                    return "{$indent}  {$key}: {$stringifiedValue}";
                default:
                    throw new Exception('Invalid state!');
            }
        }

        $value = iter($node, $depth + 1);

        return "{$indent}  {$key}: {$value}";
    }, $tree['children']);

    return "{\n" . implode("\n", $parts) . "\n" . substr($indent, 2) . "}";
}

function stringify($value, int $depth): string
{
    switch (gettype($value)) {
        case 'boolean':
            return stringifyBool($value);
        case 'NULL':
            return 'null';
        case 'array':
        case 'object':
            return stringifyArray((array) $value, $depth);
        default:
            return (string) $value;
    }
}

function stringifyArray(array $arr, int $depth): string
{
    $indent = getIndent($depth + 1);

    $parts = array_map(function ($key, $value) use ($indent, $depth) {
        $stringifiedValue = stringify($value, $depth + 1);
        return "{$indent}  {$key}: {$stringifiedValue}";
    }, array_keys($arr), $arr);

    return "{\n" . implode("\n", $parts) . "\n" . substr($indent, 2) . "}";
}

function stringifyBool(bool $value): string
{
    return $value ? 'true' : 'false';
}

function getIndent(int $depth): string
{
    return str_repeat(' ', SPACES_COUNT * $depth - 2);
}
