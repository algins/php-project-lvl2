<?php

namespace Differ\Formatters\Stylish;

function format(array $diff): string
{
    $lines = buildLines($diff);

    return render($lines);
}

function buildLines(array $diff): array
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

    return buildFromMapping($diff, $mapping);
}

function buildFromMapping(array $diff, array $mapping): array
{
    return array_reduce($diff, function (array $acc, array $item) use ($mapping): array {
        [
            'state' => $state,
            'key' => $key,
            'values' => $values,
        ] = $item;

        $formattedValues = array_map(function ($value): string {
            return stringify($value);
        }, $values);

        return array_merge($acc, $mapping[$state]($key, $formattedValues));
    }, []);
}

function stringify($value): string
{
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    };

    return (string) $value;
}

function render(array $lines): string
{
    return "{\n" . implode("\n", $lines) . "\n}\n";
}
