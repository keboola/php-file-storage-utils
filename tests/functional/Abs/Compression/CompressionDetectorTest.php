<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Functional\Abs\Compression;

use Keboola\FileStorage\Abs\Compression\CompressionDetector;
use Keboola\FileStorage\Tests\Common\Abs\ContainerFunctionalTestCase;

class CompressionDetectorTest extends ContainerFunctionalTestCase
{
    public function testIsGzip(): void
    {
        $downloader = $this->getDetector();

        $file = $this->uploadFile(__DIR__ . '/stub/lf.txt.gz', true);
        self::assertTrue($downloader->isGzip($file));
    }

    public function testIsGzipWrongExtension(): void
    {
        $downloader = $this->getDetector();

        $file = $this->uploadFile(
            __DIR__ . '/stub/lf.txt.gz',
            true,
            'lf.txt.something' // set name wo gz extension
        );
        self::assertTrue($downloader->isGzip($file));
    }

    public function testIsNotGzipWrongExtension(): void
    {
        $downloader = $this->getDetector();

        $file = $this->uploadFile(
            __DIR__ . '/../../../unit/LineEnding/stub/lf.txt',
            true // will save filename with gz extension
        );
        self::assertFalse($downloader->isGzip($file));
    }

    public function testIsNotGzip(): void
    {
        $downloader = $this->getDetector();

        $file = $this->uploadFile(__DIR__ . '/../../../unit/LineEnding/stub/lf.txt', false);
        self::assertFalse($downloader->isGzip($file));
    }

    private function getDetector(): CompressionDetector
    {
        return new CompressionDetector($this->client);
    }
}
