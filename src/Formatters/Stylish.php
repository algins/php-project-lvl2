<?php

namespace Differ\Formatters\Stylish;

use Exception;

use const Differ\Differ\TYPE_ADDED;
use const Differ\Differ\TYPE_CHANGED;
use const Differ\Differ\TYPE_NESTED;
use const Differ\Differ\TYPE_REMOVED;
use const Differ\Differ\TYPE_ROOT;
use const Differ\Differ\TYPE_UNCHANGED;

const SPACES_COUNT = 4;

function render(array $tree): string
{
    return iter($tree);
}

function iter(array $node, int $depth = 1): string
{
    $indent = getIndent($depth);

    switch ($node['type']) {
        case TYPE_ADDED:
            $value = stringify($node['value'], $depth);
            return "{$indent}+ {$node['key']}: {$value}";

        case TYPE_REMOVED:
            $value = stringify($node['value'], $depth);
            return "{$indent}- {$node['key']}: {$value}";

        case TYPE_CHANGED:
            $value1 = stringify($node['value1'], $depth);
            $value2 = stringify($node['value2'], $depth);
            return implode("\n", [
                "{$indent}- {$node['key']}: {$value1}",
                "{$indent}+ {$node['key']}: {$value2}",
            ]);

        case TYPE_UNCHANGED:
            $value = stringify($node['value'], $depth);
            return "{$indent}  {$node['key']}: {$value}";

        case TYPE_NESTED:
            $value = implode("\n", [
                '{',
                ...array_map(fn($child) => iter($child, $depth + 1), $node['children']),
                "{$indent}  }",
            ]);
            return "{$indent}  {$node['key']}: {$value}";

        case TYPE_ROOT:
            $value = implode("\n", [
                '{',
                ...array_map(fn($child) => iter($child, $depth), $node['children']),
                '}',
            ]);
            return $value;

        default:
            throw new Exception("Unknown type: {$node['type']}");
    }
}

/** @param mixed $value */
function stringify($value, int $depth): string
{
    switch (true) {
        case is_bool($value):
            return stringifyBool($value);
        case is_null($value):
            return 'null';
        case is_object($value):
        case is_array($value):
            return stringifyArray((array) $value, $depth);
        default:
            return (string) $value;
    }
}

function stringifyArray(array $arr, int $depth): string
{
    $indent = getIndent($depth);
    $nextIndent = getIndent($depth + 1);

    $mappedArr = array_map(function ($key, $item) use ($nextIndent, $depth) {
        $value = stringify($item, $depth + 1);
        return "{$nextIndent}  {$key}: {$value}";
    }, array_keys($arr), $arr);

    return implode("\n", [
        '{',
        ...$mappedArr,
        "{$indent}  }",
    ]);
}

function stringifyBool(bool $value): string
{
    return $value ? 'true' : 'false';
}

function getIndent(int $depth): string
{
    return str_repeat(' ', SPACES_COUNT * $depth - 2);
}
