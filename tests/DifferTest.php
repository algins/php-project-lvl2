<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;
use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    public function testGenDiff(): void
    {
        $path1 = $this->getFixturePath('file1.json');
        $path2 = $this->getFixturePath('file2.json');
        $diff = genDiff($path1, $path2);

        $this->assertStringEqualsFile($this->getFixturePath('diff'), $diff);
    }

    private function getFixturePath(string $filename): string
    {
        $segments = [__DIR__, 'fixtures', $filename];

        return implode(DIRECTORY_SEPARATOR, $segments);
    }
}
