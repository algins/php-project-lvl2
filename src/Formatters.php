<?php

namespace Differ\Formatters;

use function Differ\Formatters\Stylish\format as formatStylish;
use function Differ\Formatters\Plain\format as formatPlain;
use function Differ\Formatters\Json\format as formatJson;

const FORMAT_STYLISH = 'stylish';
const FORMAT_PLAIN = 'plain';
const FORMAT_JSON = 'json';

function format(?string $formatName): callable
{
    $formatters = [
        FORMAT_STYLISH => function (array $diff): string {
            return formatStylish($diff);
        },
        FORMAT_PLAIN => function (array $diff): string {
            return formatPlain($diff);
        },
        FORMAT_JSON => function (array $diff): string {
            return formatJson($diff);
        },
    ];

    return $formatters[$formatName ?? getDefaultFormat()];
}


function getDefaultFormat(): string
{
    return FORMAT_STYLISH;
}
