<?php

namespace Differ\Differ;

use function Funct\Collection\union;
use function Differ\Parsers\parse;

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
        if (!array_key_exists($key, $arr1)) {
            $currentValue = is_bool($arr2[$key]) ? $arr2[$key] ? 'true' : 'false' : $arr2[$key];
            $acc[] = "  + {$key}: {$currentValue}";
            return $acc;
        }

        if (!array_key_exists($key, $arr2)) {
            $previousValue = is_bool($arr1[$key]) ? $arr1[$key] ? 'true' : 'false' : $arr1[$key];
            $acc[] = "  - {$key}: {$previousValue}";
            return $acc;
        }

        if ($arr1[$key] !== $arr2[$key]) {
            $previousValue = is_bool($arr1[$key]) ? $arr1[$key] ? 'true' : 'false' : $arr1[$key];
            $currentValue = is_bool($arr2[$key]) ? $arr2[$key] ? 'true' : 'false' : $arr2[$key];
            $acc[] = "  - {$key}: {$previousValue}";
            $acc[] = "  + {$key}: {$currentValue}";
            return $acc;
        }

        $currentValue = is_bool($arr2[$key]) ? $arr2[$key] ? 'true' : 'false' : $arr2[$key];
        $acc[] = "    {$key}: {$currentValue}";

        return $acc;
    }, []);

    return "{\n" . implode("\n", $diff) . "\n}\n";
}

function readFile(string $path): string
{
    $realPath = realpath($path);
    $data = file_get_contents($realPath);

    return $data;
}
