<?php

namespace Differ\Differ;

use Exception;

use function Differ\Formatters\format;
use function Differ\Parsers\parse;
use function Functional\sort;

use const Differ\Formatters\FORMAT_STYLISH;

const STATE_ADDED = 'added';
const STATE_CHANGED = 'changed';
const STATE_REMOVED = 'removed';
const STATE_UNCHANGED = 'unchanged';
const TYPE_INTERNAL = 'internal';
const TYPE_LEAF = 'leaf';
const TYPE_ROOT = 'root';

function genDiff(string $path1, string $path2, string $formatName = FORMAT_STYLISH): string
{
    $obj1 = parse(
        readFile($path1),
        getFileType($path1)
    );

    $obj2 = parse(
        readFile($path2),
        getFileType($path2)
    );

    $diff = buildTree($obj1, $obj2);

    return format($diff, $formatName);
}

function buildTree(object $obj1, object $obj2): array
{
    return [
        'type' => TYPE_ROOT,
        'children' => buildNodes($obj1, $obj2),
    ];
}

function buildNodes(object $obj1, object $obj2): array
{
    $keys = unionKeys($obj1, $obj2);

    return array_map(function ($key) use ($obj1, $obj2): array {
        $value1 = $obj1->{$key} ?? null;
        $value2 = $obj2->{$key} ?? null;

        if (!property_exists($obj1, $key)) {
            return [
                'key' => $key,
                'type' => TYPE_LEAF,
                'state' => STATE_ADDED,
                'value' => $value2,
            ];
        } elseif (!property_exists($obj2, $key)) {
            return [
                'key' => $key,
                'type' => TYPE_LEAF,
                'state' => STATE_REMOVED,
                'value' => $value1,
            ];
        } elseif (is_object($value1) && is_object($value2)) {
            return [
                'key' => $key,
                'type' => TYPE_INTERNAL,
                'children' => buildNodes($value1, $value2),
            ];
        } elseif ($value1 !== $value2) {
            return [
                'key' => $key,
                'type' => TYPE_LEAF,
                'state' => STATE_CHANGED,
                'value1' => $value1,
                'value2' => $value2,
            ];
        } else {
            return [
                'key' => $key,
                'type' => TYPE_LEAF,
                'state' => STATE_UNCHANGED,
                'value1' => $value1,
                'value2' => $value2,
            ];
        }
    }, $keys);
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
