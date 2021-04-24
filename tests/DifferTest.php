<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;
use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function testGenDiff(string $file1, string $file2, ?string $formatter): void
    {
        $filepath1 = $this->getFixturePath($file1);
        $filepath2 = $this->getFixturePath($file2);
        $diff = genDiff($filepath1, $filepath2, $formatter);

        $this->assertStringEqualsFile($this->getFixturePath('diff'), $diff);
    }

    public function provider(): array
    {
        return [
            ['file1.json', 'file2.json', null],
            ['file1.yaml', 'file2.yaml', null],
            ['file1.json', 'file2.json', 'stylish'],
            ['file1.yaml', 'file2.yaml', 'stylish'],
        ];
    }

    private function getFixturePath(string $filename): string
    {
        $segments = [__DIR__, 'fixtures', $filename];

        return implode(DIRECTORY_SEPARATOR, $segments);
    }
}
