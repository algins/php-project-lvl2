<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

const TYPE_JSON = 'json';
const TYPE_YAML = 'yaml';

function parse(string $data, string $type): object
{
    switch ($type) {
        case TYPE_JSON:
            return parseJson($data);
        case TYPE_YAML:
            return parseYaml($data);
        default:
            throw new Exception('Invalid type!');
    }
}

function parseJson(string $data): object
{
    return json_decode($data);
}

function parseYaml(string $data): object
{
    return Yaml::parse($data, Yaml::PARSE_OBJECT_FOR_MAP);
}
