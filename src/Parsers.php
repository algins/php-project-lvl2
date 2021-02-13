<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parse(string $data, string $format): array
{
    $mapping = [
        'json' => function (string $data): array {
            return parseJson($data);
        },
        'yaml' => function (string $data): array {
            return parseYaml($data);
        }
    ];

    return $mapping[$format]($data);
}

function parseJson(string $data): array
{
    return json_decode($data, true);
}

function parseYaml(string $data): array
{
    return Yaml::parse($data);
}
