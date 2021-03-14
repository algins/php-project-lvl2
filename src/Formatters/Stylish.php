<?php

namespace Differ\Formatters\Stylish;

function format(array $diff): string
{
    $output = prepareOutput($diff);

    return render($output);
}

function prepareOutput(array $diff): array
{
    return array_reduce($diff, function (array $acc, array $item): array {
        [
            'state' => $state,
            'key' => $key,
            'values' => $values,
        ] = $item;

        $stringifiedValues = stringifyValues($values);
        $lines = buildLines($state, $key, $stringifiedValues);

        return array_merge($acc, $lines);
    }, []);
}

function buildLines(string $state, string $key, array $values): array
{
    switch ($state) {
        case 'added':
            ['current' => $current] = $values;
            $lines = ["  + {$key}: {$current}"];
            break;
        case 'removed':
            ['previous' => $previous] = $values;
            $lines = ["  - {$key}: {$previous}"];
            break;
        case 'changed':
            [
                'previous' => $previous,
                'current' => $current
            ] = $values;

            $lines = [
                "  - {$key}: {$previous}",
                "  + {$key}: {$current}",
            ];
            break;
        case 'unchanged':
            ['current' => $current] = $values;
            $lines = ["    {$key}: {$current}"];
            break;
        default:
            $lines = [];
            break;
    }

    return $lines;
}

function stringifyValues(array $values): array
{
    return array_map(function ($value): string {
        return is_bool($value) ? stringifyBool($value) : (string) $value;
    }, $values);
}

function stringifyBool(bool $value): string
{
    return $value ? 'true' : 'false';
}

function render(array $lines): string
{
    return "{\n" . implode("\n", $lines) . "\n}\n";
}
