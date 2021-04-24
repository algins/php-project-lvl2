<?php

namespace Differ\Differ;

use function Funct\Collection\union;
use function Differ\Parsers\parse;
use function Differ\Formatters\Stylish\format as formatStylish;

const TYPE_FLAT = 'flat';
const TYPE_NESTED = 'nested';
const STATE_ADDED = 'added';
const STATE_REMOVED = 'removed';
const STATE_CHANGED = 'changed';
const STATE_UNCHANGED = 'unchanged';
const FORMATTER_STYLISH = 'stylish';

function genDiff(string $path1, string $path2, ?string $formatter): string
{
    $obj1 = parse(
        readFile($path1),
        getFileType($path1)
    );

    $obj2 = parse(
        readFile($path2),
        getFileType($path2)
    );

    $diff = compare($obj1, $obj2);

    return format($formatter)($diff);
}

function compare(object $obj1, object $obj2): array
{
    $arr1 = (array) $obj1;
    $arr2 = (array) $obj2;

    $keys = unionKeys($arr1, $arr2);

    return array_reduce($keys, function ($acc, $key) use ($arr1, $arr2) {
        $compared['key'] = $key;

        if (isNested($key, $arr1, $arr2)) {
            $compared['type'] = TYPE_NESTED;
            $compared['diff'] = compare($arr1[$key], $arr2[$key]);
        } else {
            $compared['type'] = TYPE_FLAT;
            $compared['state'] = getState($key, $arr1, $arr2);
            $compared['values'] = getValues($key, $arr1, $arr2);
        }

        $acc[] = $compared;

        return $acc;
    }, []);
}

function getState(string $key, array $arr1, array $arr2): string
{
    switch (true) {
        case !array_key_exists($key, $arr1):
            $state = STATE_ADDED;
            break;
        case !array_key_exists($key, $arr2):
            $state = STATE_REMOVED;
            break;
        case is_object($arr1[$key]) && is_object($arr2[$key]):
            $state = STATE_NESTED;
            break;
        case $arr1[$key] !== $arr2[$key]:
            $state = STATE_CHANGED;
            break;
        default:
            $state = STATE_UNCHANGED;
            break;
    }

    return $state;
}

function isNested(string $key, array $arr1, array $arr2): bool
{
    return is_object($arr1[$key]) && is_object($arr2[$key]);
}

function getValues(string $key, array $arr1, array $arr2): array
{
    $values = [];

    if (array_key_exists($key, $arr1)) {
        $values['previous'] = $arr1[$key];
    }

    if (array_key_exists($key, $arr2)) {
        $values['current'] = $arr2[$key];
    }

    return $values;
}

function unionKeys(array $arr1, array $arr2): array
{
    $keys = union(
        array_keys($arr1),
        array_keys($arr2)
    );

    sort($keys);

    return $keys;
}

function readFile(string $path): string
{
    $realPath = realpath($path);
    $data = file_get_contents($realPath);

    return $data;
}

function getFileType(string $path): string
{
    return pathinfo($path, PATHINFO_EXTENSION);
}

function format(?string $formatter)
{
    $defaultFormatter = FORMATTER_STYLISH;

    $formatters = [
        FORMATTER_STYLISH => function (array $diff) {
            return formatStylish($diff);
        },
    ];

    return $formatters[$formatter ?? $defaultFormatter];
}
