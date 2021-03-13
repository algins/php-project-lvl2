<?php

namespace Differ\Differ;

use function Funct\Collection\union;
use function Differ\Parsers\parse;
use function Differ\Formatters\Stylish\format;

function genDiff(string $filepath1, string $filepath2, ?string $format): string
{
    $data1 = readFile($filepath1);
    $data2 = readFile($filepath2);

    $arr1 = parse($data1, $format);
    $arr2 = parse($data2, $format);

    $keys1 = array_keys($arr1);
    $keys2 = array_keys($arr2);

    $keys = union($keys1, $keys2);
    sort($keys);

    $diff =  array_reduce($keys, function ($acc, $key) use ($arr1, $arr2) {
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

    return format($diff);
}

function readFile(string $path): string
{
    $realPath = realpath($path);
    $data = file_get_contents($realPath);

    return $data;
}
