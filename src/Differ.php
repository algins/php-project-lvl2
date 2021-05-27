<?php

namespace Differ\Differ;

use function Differ\Parsers\parse;
use function Differ\Formatters\format;

const TYPE_FLAT = 'flat';
const TYPE_NESTED = 'nested';
const STATE_ADDED = 'added';
const STATE_REMOVED = 'removed';
const STATE_UPDATED = 'updated';
const STATE_UNCHANGED = 'unchanged';

function genDiff(string $path1, string $path2, ?string $formatName = null): string
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

    return format($formatName)($diff);
}

function compare(object $obj1, object $obj2): array
{
    $arr1 = (array) $obj1;
    $arr2 = (array) $obj2;

    $keys = unionKeys($arr1, $arr2);

    return array_reduce($keys, function ($acc, $key) use ($arr1, $arr2): array {
        if (isNested($key, $arr1, $arr2)) {
            $compared = [
                'key' => $key,
                'type' => TYPE_NESTED,
                'diff' => compare($arr1[$key], $arr2[$key]),
            ];
        } else {
            $compared = [
                'key' => $key,
                'type' => TYPE_FLAT,
                'state' => getState($key, $arr1, $arr2),
                'values' => getValues($key, $arr1, $arr2),
            ];
        }

        return [...$acc, $compared];
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
        case $arr1[$key] !== $arr2[$key]:
            $state = STATE_UPDATED;
            break;
        default:
            $state = STATE_UNCHANGED;
            break;
    }

    return $state;
}

function isNested(string $key, array $arr1, array $arr2): bool
{
    if (!array_key_exists($key, $arr1) || !array_key_exists($key, $arr2)) {
        return false;
    }

    return is_object($arr1[$key]) && is_object($arr2[$key]);
}

function getValues(string $key, array $arr1, array $arr2): array
{
    switch (true) {
        case array_key_exists($key, $arr1) && array_key_exists($key, $arr2):
            $values = [
                'previous' => $arr1[$key],
                'current' => $arr2[$key],
            ];
            break;
        case array_key_exists($key, $arr1):
            $values = ['previous' => $arr1[$key]];
            break;
        case array_key_exists($key, $arr2):
            $values = ['current' => $arr2[$key]];
            break;
        default:
            $values = [];
            break;
    }

    return $values;
}

function unionKeys(array $arr1, array $arr2): array
{
    $keys = [
        ...array_keys($arr1),
        ...array_keys($arr2),
    ];

    $uniqueKeys = array_unique($keys);

    sort($uniqueKeys);

    return $uniqueKeys;
}

function readFile(string $path): string
{
    $realPath = realpath($path);

    if ($realPath === false) {
        throw new \Exception('Path not exists.');
    }

    $data = file_get_contents($realPath);

    if ($data === false) {
        throw new \Exception('Data read error.');
    }

    return $data;
}

function getFileType(string $path): string
{
    return pathinfo($path, PATHINFO_EXTENSION);
}
