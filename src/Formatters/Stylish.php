<?php

namespace Differ\Formatters\Stylish;

function format(array $diff): string
{
    $mapping = [
        'added' => function (string $key, array $values): array {
            ['current' => $current] = $values;
            return [
                "  + {$key}: {$current}",
            ];
        },
        'removed' => function (string $key, array $values): array {
            ['previous' => $previous] = $values;
            return [
                "  - {$key}: {$previous}",
            ];
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
            return [
                "    {$key}: {$current}",
            ];
        },
    ];

    $formattedDiff = array_reduce($diff, function (array $acc, array $item) use ($mapping): array {
        [
            'state' => $state,
            'key' => $key,
            'values' => $values,
        ] = $item;

        $formattedValues = array_map(function ($value) {
            return stringify($value);
        }, $values);

        return array_merge($acc, $mapping[$state]($key, $formattedValues));
    }, []);

    return "{\n" . implode("\n", $formattedDiff) . "\n}\n";
}

function stringify($value): string
{
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    };

    return (string) $value;
}
