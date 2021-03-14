<?php

namespace Differ\Formatters\Stylish;

$mapping = [
    'added' => function (string $key, array $values): array {
        ['current' => $current] = $values;
        return ["  + {$key}: {$current}"];
    },
    'removed' => function (string $key, array $values): array {
        ['previous' => $previous] = $values;
        return ["  - {$key}: {$previous}"];
    },
    'changed' => function (string $key, array $values): array {
        [
            'current' => $current,
            'previous' => $previous,
        ] = $values;
        return [
            "  - {$key}: {$previous}",
            "  + {$key}: {$current}",
        ];
    },
    'unchanged' => function (string $key, array $values): array {
        ['current' => $current] = $values;
        return ["    {$key}: {$current}"];
    },
];

function format(array $diff): string
{
    $lines = buildLines($diff, $mapping);

    return render($lines);
}

function buildLines(array $diff, array $mapping): array
{
    return array_reduce($diff, function (array $acc, array $item) use ($mapping): array {
        [
            'state' => $state,
            'key' => $key,
            'values' => $values,
        ] = $item;

        $stringifiedValues = stringifyValues($values);

        return array_merge($acc, $mapping[$state]($key, $stringifiedValues));
    }, []);
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
