<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;
use function Differ\Differ\genDiff;
use function Differ\Formatters\getDefaultFormat;
use const Differ\Formatters\FORMAT_STYLISH;
use const Differ\Formatters\FORMAT_PLAIN;
use const Differ\Formatters\FORMAT_JSON;

class DifferTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function testGenDiff(string $file1, string $file2, ?string $formatName): void
    {

        $inputFilepath1 = $this->getFixturePath($file1);
        $inputFilepath2 = $this->getFixturePath($file2);
        $diff = genDiff($inputFilepath1, $inputFilepath2, $formatName);
        $outputFilepath = $this->getFixturePath($formatName ?? getDefaultFormat());

        $this->assertStringEqualsFile($outputFilepath, $diff);
    }

    public function provider(): array
    {
        return [
            ['file1.json', 'file2.json', null],
            ['file1.yaml', 'file2.yaml', null],
            ['file1.json', 'file2.json', FORMAT_STYLISH],
            ['file1.json', 'file2.json', FORMAT_PLAIN],
            ['file1.json', 'file2.json', FORMAT_JSON],
        ];
    }

    private function getFixturePath(string $filename): string
    {
        $segments = [__DIR__, 'fixtures', $filename];

        return implode(DIRECTORY_SEPARATOR, $segments);
    }
}
