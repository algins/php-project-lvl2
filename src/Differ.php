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
        switch (true) {
            case !array_key_exists($key, $arr1):
                $state = 'added';
                $values = ['current' => $arr2[$key]];
                break;
            case !array_key_exists($key, $arr2):
                $state = 'removed';
                $values = ['previous' => $arr1[$key]];
                break;
            case $arr1[$key] !== $arr2[$key]:
                $state = 'changed';
                $values = [
                    'previous' => $arr1[$key],
                    'current' => $arr2[$key],
                ];
                break;
            default:
                $state = 'unchanged';
                $values = [
                    'previous' => $arr1[$key],
                    'current' => $arr2[$key],
                ];
                break;
        }

        $acc[] = [
            'state' => $state,
            'key' => $key,
            'values' => $values,
        ];

        return $acc;
    }, []);
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
