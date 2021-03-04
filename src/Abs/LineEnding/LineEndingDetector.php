<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Abs\LineEnding;

use Keboola\FileStorage\Abs\Download\PartialFileDownloader;
use Keboola\FileStorage\LineEnding\LineEndingDetectorInterface;
use Keboola\FileStorage\LineEnding\StringLineEndingDetectorHelper;
use Keboola\FileStorage\Path\RelativePathInterface;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

final class LineEndingDetector implements LineEndingDetectorInterface
{
    /** @var PartialFileDownloader */
    private $downloader;

    public function __construct(PartialFileDownloader $downloader)
    {
        $this->downloader = $downloader;
    }

    public static function createForClient(BlobRestProxy $blobClient): self
    {
        return new self(
            new PartialFileDownloader($blobClient)
        );
    }

    /**
     * @return StringLineEndingDetectorHelper::EOL_*
     */
    public function getLineEnding(
        RelativePathInterface $path,
        int $bytes = LineEndingDetectorInterface::BYTES_RANGE_END
    ): string {
        $downloadResult = $this->downloader->downloadBytes($path, $bytes);

        return StringLineEndingDetectorHelper::getLineEndingFromString($downloadResult);
    }
}
