<?php

namespace Differ\Formatters\Stylish;

use const Differ\Differ\STATE_ADDED;
use const Differ\Differ\STATE_CHANGED;
use const Differ\Differ\STATE_REMOVED;
use const Differ\Differ\STATE_UNCHANGED;
use const Differ\Differ\TYPE_FLAT;
use const Differ\Differ\TYPE_NESTED;

const INDENT_SIZE = 4;
const PREFIX_ADDED = '+';
const PREFIX_REMOVED = '-';

function format(array $diff, int $indentSize = 0): string
{
    $types = [
        TYPE_FLAT => function (string $key, array $item) use ($indentSize): array {
            ['state' => $state, 'values' => $values] = $item;
            return buildDiffLines($state, $key, $values, $indentSize);
        },
        TYPE_NESTED => function (string $key, array $item) use ($indentSize): array {
            ['children' => $diff] = $item;
            $formattedDiff = format($diff, $indentSize + INDENT_SIZE);
            return [indent("{$key}: {$formattedDiff}", $indentSize + INDENT_SIZE) . "\n"];
        },
    ];

    $lines = array_reduce($diff, function ($acc, $item) use ($types): array {
        ['type' => $type, 'key' => $key] = $item;
        return [...$acc, ...$types[$type]($key, $item)];
    }, []);

    return renderLines($lines, $indentSize);
}

function buildArrayLines(array $values, int $indentSize = 0): array
{
    $list = array_map(function ($key, $value): array {
        return ['key' => $key, 'value' => $value, 'prefix' => null];
    }, array_keys($values), $values);

    return buildLines($list, $indentSize + INDENT_SIZE);
}

function buildDiffLines(string $state, string $key, array $values, int $indentSize = 0): array
{
    switch ($state) {
        case STATE_ADDED:
            $list = [
                ['key' => $key, 'value' => $values['second'], 'prefix' => PREFIX_ADDED],
            ];
            break;
        case STATE_REMOVED:
            $list = [
                ['key' => $key, 'value' => $values['first'], 'prefix' => PREFIX_REMOVED],
            ];
            break;
        case STATE_CHANGED:
            $list = [
                ['key' => $key, 'value' => $values['first'], 'prefix' => PREFIX_REMOVED],
                ['key' => $key, 'value' => $values['second'], 'prefix' => PREFIX_ADDED],
            ];
            break;
        case STATE_UNCHANGED:
            $list = [
                ['key' => $key, 'value' => $values['second'], 'prefix' => null],
            ];
            break;
        default:
            $list = [];
    }

    return buildLines($list, $indentSize + INDENT_SIZE);
}

function buildLines(array $list, int $initialIndentSize = 0): array
{
    return array_map(function ($parts) use ($initialIndentSize): string {
        ['key' => $key, 'value' => $value, 'prefix' => $prefix] = $parts;
        $stringifiedValue = stringify($value, $initialIndentSize);
        $indentSize = $prefix ? $initialIndentSize - strlen($prefix) - 1 : $initialIndentSize;
        return indent(ltrim("{$prefix} {$key}: {$stringifiedValue}"), $indentSize) . "\n";
    }, $list);
}

function renderLines(array $lines, int $indentSize = 0): string
{
    $firstLine = "{\n";
    $lastLine = indent("}", $indentSize);

    return implode('', [$firstLine, ...$lines, $lastLine]);
}

/** @param mixed $value */
function stringify($value, int $indentSize = 0): string
{
    switch (true) {
        case is_bool($value):
            $stringifiedValue = stringifyBool($value);
            break;
        case is_null($value):
            $stringifiedValue = 'null';
            break;
        case is_array($value) || is_object($value):
            $stringifiedValue = stringifyArray((array) $value, $indentSize);
            break;
        default:
            $stringifiedValue = (string) $value;
    }

    return $stringifiedValue;
}

function stringifyArray(array $values, int $indentSize = 0): string
{
    $lines = buildArrayLines($values, $indentSize);

    return renderLines($lines, $indentSize);
}

function stringifyBool(bool $value): string
{
    return $value ? 'true' : 'false';
}

function indent(string $value, int $indentSize): string
{
    return str_repeat(' ', $indentSize) . $value;
}
