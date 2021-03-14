<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parse(string $data, string $type): object
{
    $mapping = [
        'json' => function (string $data): object {
            return parseJson($data);
        },
        'yaml' => function (string $data): object {
            return parseYaml($data);
        }
    ];

    return $mapping[$type]($data);
}

function parseJson(string $data): object
{
    return json_decode($data);
}

function parseYaml(string $data): object
{
    return Yaml::parse($data, Yaml::PARSE_OBJECT_FOR_MAP);
}
