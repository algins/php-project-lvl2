<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

const TYPE_JSON = 'json';
const TYPE_YAML = 'yaml';

function parse(?string $data, string $type): object
{
    $parsers = [
        TYPE_JSON => function (?string $data): object {
            return parseJson($data);
        },
        TYPE_YAML => function (?string $data): object {
            return parseYaml($data);
        }
    ];

    return $parsers[$type]($data);
}

function parseJson(?string $data): object
{
    return json_decode($data);
}

function parseYaml(?string $data): object
{
    return Yaml::parse($data, Yaml::PARSE_OBJECT_FOR_MAP);
}
