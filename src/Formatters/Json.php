<?php

namespace Differ\Formatters\Json;

function render(array $diff): string
{
    return json_encode($diff, JSON_PRETTY_PRINT);
}
