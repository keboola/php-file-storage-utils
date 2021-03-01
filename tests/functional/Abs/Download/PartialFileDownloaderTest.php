<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Functional\Abs;

use Keboola\FileStorage\Abs\AbsProvider;
use Keboola\FileStorage\Abs\Download\PartialFileDownloader;
use Keboola\FileStorage\FileNotFoundException;
use Keboola\FileStorage\Path\RelativePath;
use Keboola\FileStorage\Tests\Common\Abs\ContainerFunctionalTestCase;

class PartialFileDownloaderTest extends ContainerFunctionalTestCase
{
    public function testDownloadBytesFileNotFound(): void
    {
        $downloader = $this->getDownloader();

        $this->expectException(FileNotFoundException::class);
        $downloader->downloadBytes(
            RelativePath::create(new AbsProvider(), $this->getContainerName(), '', 'notexistingFile')
        );
    }

    public function testDownloadBytes(): void
    {
        $downloader = $this->getDownloader();
        $file = $this->uploadString(
            bin2hex(random_bytes(350000)) // create 700k bytes file
        );

        $result = $downloader->downloadBytes($file);

        self::assertEquals(
            (655360 + 1), // PartialFileDownloader::BYTES_RANGE_END + zero byte
            strlen($result)
        );
    }

    public function testDownloadBytesCustomRange(): void
    {
        $downloader = $this->getDownloader();
        $file = $this->uploadString(
            bin2hex(random_bytes(1000))
        );

        $result = $downloader->downloadBytes($file, 100);

        self::assertEquals(
            (100 + 1), // 100 + zero byte
            strlen($result)
        );
    }

    private function getDownloader(): PartialFileDownloader
    {
        return new PartialFileDownloader($this->client);
    }
}
