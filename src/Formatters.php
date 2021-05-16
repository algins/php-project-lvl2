<?php

namespace Differ\Formatters;

use function Differ\Formatters\Stylish\format as formatStylish;
use function Differ\Formatters\Plain\format as formatPlain;

const FORMAT_STYLISH = 'stylish';
const FORMAT_PLAIN = 'plain';

function format(?string $formatName)
{
    $formatters = [
        FORMAT_STYLISH => function (array $diff) {
            return formatStylish($diff);
        },
        FORMAT_PLAIN => function (array $diff) {
            return formatPlain($diff);
        },
    ];

    return $formatters[$formatName ?? getDefaultFormat()];
}


function getDefaultFormat(): string
{
    return FORMAT_STYLISH;
}
