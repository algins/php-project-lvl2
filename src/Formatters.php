<?php

namespace Differ\Formatters;

use Exception;

use function Differ\Formatters\Json\render as formatJson;
use function Differ\Formatters\Plain\render as formatPlain;
use function Differ\Formatters\Stylish\render as formatStylish;

const FORMAT_STYLISH = 'stylish';
const FORMAT_PLAIN = 'plain';
const FORMAT_JSON = 'json';

function render(array $diff, string $format): string
{
    switch ($format) {
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
