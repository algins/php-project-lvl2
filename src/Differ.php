<?php

namespace Differ\Differ;

use Exception;

use function Differ\Formatters\format;
use function Differ\Parsers\parse;
use function Functional\sort;

const STATE_ADDED = 'added';
const STATE_CHANGED = 'changed';
const STATE_REMOVED = 'removed';
const STATE_UNCHANGED = 'unchanged';
const TYPE_FLAT = 'flat';
const TYPE_NESTED = 'nested';

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
    $keys = unionKeys($obj1, $obj2);

    return array_reduce($keys, function ($acc, $key) use ($obj1, $obj2): array {
        if (!property_exists($obj1, $key)) {
            $compared = [
                'key' => $key,
                'type' => TYPE_FLAT,
                'state' => STATE_ADDED,
                'values' => ['second' => $obj2->{$key}],
            ];
        } elseif (!property_exists($obj2, $key)) {
            $compared = [
                'key' => $key,
                'type' => TYPE_FLAT,
                'state' => STATE_REMOVED,
                'values' => ['first' => $obj1->{$key}],
            ];
        } elseif (is_object($obj1->{$key}) && is_object($obj2->{$key})) {
            $compared = [
                'key' => $key,
                'type' => TYPE_NESTED,
                'children' => compare($obj1->{$key}, $obj2->{$key}),
            ];
        } elseif ($obj1->{$key} !== $obj2->{$key}) {
            $compared = [
                'key' => $key,
                'type' => TYPE_FLAT,
                'state' => STATE_CHANGED,
                'values' => ['first' => $obj1->{$key}, 'second' => $obj2->{$key}],
            ];
        } else {
            $compared = [
                'key' => $key,
                'type' => TYPE_FLAT,
                'state' => STATE_UNCHANGED,
                'values' => ['first' => $obj1->{$key}, 'second' => $obj2->{$key}],
            ];
        }

        return [...$acc, $compared];
    }, []);
}

function unionKeys(object $obj1, object $obj2): array
{
    $keys = [
        ...array_keys((array) $obj1),
        ...array_keys((array) $obj2),
    ];

    $uniqueKeys = array_unique($keys);
    $sortedKeys = sort($uniqueKeys, fn($left, $right) => strcmp($left, $right));

    return $sortedKeys;
}

function readFile(string $path): string
{
    $realPath = realpath($path);

    if ($realPath === false) {
        throw new Exception('Path not exists.');
    }

    $data = file_get_contents($realPath);

    if ($data === false) {
        throw new Exception('Data read error.');
    }

    return $data;
}

function getFileType(string $path): string
{
    return pathinfo($path, PATHINFO_EXTENSION);
}
