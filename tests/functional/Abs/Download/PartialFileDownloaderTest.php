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

    public function testDownloadBytesGzip(): void
    {
        $downloader = $this->getDownloader();
        /** @var string $tmpFilePath */
        $tmpFilePath = tempnam(sys_get_temp_dir(), 'testDownloadBytesGzip');
        file_put_contents(
            sprintf('compress.zlib://%s', $tmpFilePath),
            bin2hex(random_bytes(200))
        );

        $file = $this->uploadFile($tmpFilePath, true);

        $result = $downloader->downloadBytes($file, 100);

        self::assertEquals(
            100,
            strlen($result)
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
            655360,
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
            100,
            strlen($result)
        );
    }

    private function getDownloader(): PartialFileDownloader
    {
        return new PartialFileDownloader($this->client);
    }
}
