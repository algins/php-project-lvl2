<?php

namespace Differ\Differ;

use function Funct\Collection\union;
use function Differ\Parsers\parse;
use function Differ\Formatters\Stylish\format;

function genDiff(string $path1, string $path2, ?string $format): string
{
    $arr1 = (array) parse(
        readFile($path1),
        getFileType($path1)
    );

    $arr2 = (array) parse(
        readFile($path2),
        getFileType($path2)
    );

    $diff = compare($arr1, $arr2);

    return format($diff);
}

function compare(array $arr1, array $arr2): array
{
    $keys = unionKeys($arr1, $arr2);

    return array_reduce($keys, function ($acc, $key) use ($arr1, $arr2) {
        $acc[] = [
            'key' => $key,
            'state' => getKeyState($key, $arr1, $arr2),
            'values' => getKeyValues($key, $arr1, $arr2),
        ];

        return $acc;
    }, []);
}

function getKeyState(string $key, array $arr1, array $arr2): string
{
    switch (true) {
        case !array_key_exists($key, $arr1):
            $state = 'added';
            break;
        case !array_key_exists($key, $arr2):
            $state = 'removed';
            break;
        case $arr1[$key] !== $arr2[$key]:
            $state = 'changed';
            break;
        default:
            $state = 'unchanged';
            break;
    }

    return $state;
}

function getKeyValues(string $key, array $arr1, array $arr2): array
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
