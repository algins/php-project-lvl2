<?php

namespace Differ\Formatters;

use function Differ\Formatters\Json\format as formatJson;
use function Differ\Formatters\Plain\format as formatPlain;
use function Differ\Formatters\Stylish\format as formatStylish;

const FORMAT_STYLISH = 'stylish';
const FORMAT_PLAIN = 'plain';
const FORMAT_JSON = 'json';

function format(array $diff, string $formatName): string
{
    switch ($formatName) {
        case FORMAT_STYLISH:
            return formatStylish($diff);
        case FORMAT_PLAIN:
            return formatPlain($diff);
        case FORMAT_JSON:
            return formatJson($diff);
        default:
            throw new Exception('Invalid format!');
    }
}
