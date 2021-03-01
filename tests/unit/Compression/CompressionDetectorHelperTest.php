<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Unit\Compression;

use Keboola\FileStorage\Compression\CompressionDetectorHelper;
use PHPUnit\Framework\TestCase;

class CompressionDetectorHelperTest extends TestCase
{
    public function testIsGzipped(): void
    {
        self::assertTrue(CompressionDetectorHelper::isGzipped('file.gz'));
        self::assertTrue(CompressionDetectorHelper::isGzipped('file.gzip'));
    }

    public function testIsNotGzipped(): void
    {
        self::assertFalse(CompressionDetectorHelper::isGzipped('file.zip'));
    }
}
