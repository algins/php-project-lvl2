<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

use const Differ\Formatters\FORMAT_STYLISH;
use const Differ\Formatters\FORMAT_PLAIN;
use const Differ\Formatters\FORMAT_JSON;

class DifferTest extends TestCase
{
    /**
     * @dataProvider fileTypeProvider
     */
    public function testGenDiffForDifferentFileTypes(string $fileType): void
    {

        $inputFilepath1 = $this->getFixturePath("file1.{$fileType}");
        $inputFilepath2 = $this->getFixturePath("file2.{$fileType}");
        $diff = genDiff($inputFilepath1, $inputFilepath2);
        $outputFilepath = $this->getFixturePath(FORMAT_STYLISH);

        $this->assertStringEqualsFile($outputFilepath, $diff);
    }

    public function fileTypeProvider(): array
    {
        return [
            ['json'],
            ['yaml'],
        ];
    }

    /**
     * @dataProvider outputFormatProvider
     */
    public function testGenDiffForDifferentOutputFormats(string $outputFormat): void
    {
        $inputFilepath1 = $this->getFixturePath('file1.json');
        $inputFilepath2 = $this->getFixturePath('file2.json');
        $diff = genDiff($inputFilepath1, $inputFilepath2, $outputFormat);
        $outputFilepath = $this->getFixturePath($outputFormat);

        $this->assertStringEqualsFile($outputFilepath, $diff);
    }

    public function outputFormatProvider(): array
    {
        return [
            [FORMAT_STYLISH],
            [FORMAT_PLAIN],
            [FORMAT_JSON],
        ];
    }

    private function getFixturePath(string $filename): string
    {
        $segments = [__DIR__, 'fixtures', $filename];

        return implode(DIRECTORY_SEPARATOR, $segments);
    }
}
