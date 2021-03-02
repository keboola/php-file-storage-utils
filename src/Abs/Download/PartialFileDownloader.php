<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Abs\Download;

use Keboola\FileStorage\FileNotFoundException;
use Keboola\FileStorage\Compression\CompressionDetectorHelper;
use Keboola\FileStorage\Path\RelativePathInterface;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\GetBlobOptions;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Common\Models\Range;
use Symfony\Component\Process\Process;

final class PartialFileDownloader
{
    private const ERR_CODE_INVALID_PAGE_RANGE = 416;
    private const ERR_CODE_BLOB_NOT_FOUND = 404;
    private const BYTES_RANGE_START = 0;
    public const BYTES_RANGE_END = 655360;

    /** @var BlobRestProxy */
    private $blobClient;

    public function __construct(BlobRestProxy $blobClient)
    {
        $this->blobClient = $blobClient;
    }

    public function downloadBytes(RelativePathInterface $path, int $bytes = self::BYTES_RANGE_END): string
    {
        $tmpFilePath = null;
        $options = new GetBlobOptions();
        $isCompressed = CompressionDetectorHelper::isGzipped($path->getPathnameWithoutRoot());

        if (!$isCompressed) {
            // download only certain range if dealing with text files
            $options->setRange(new Range(self::BYTES_RANGE_START, $bytes - 1));
        }

        try {
            $result = $this->blobClient->getBlob(
                $path->getRoot(),
                $path->getPathnameWithoutRoot(),
                $options
            );
        } catch (ServiceException $e) {
            if ($e->getCode() === self::ERR_CODE_BLOB_NOT_FOUND) {
                throw new FileNotFoundException($path, $e);
            }
            if ($e->getCode() === self::ERR_CODE_INVALID_PAGE_RANGE) {
                // file is empty
                return '';
            }

            throw $e;
        }

        if ($isCompressed) {
            /** @var string $tmpFilePath */
            $tmpFilePath = tempnam(sys_get_temp_dir(), 'PartialFileDownloader');
            file_put_contents($tmpFilePath, $result->getContentStream());
            $process = Process::fromShellCommandline(sprintf('zcat %s | head -c %s', $tmpFilePath, $bytes));
            $process->mustRun();
            $body = $process->getOutput();
            unlink($tmpFilePath);
        } else {
            $body = (string) fgets($result->getContentStream());
        }

        return $body;
    }
}
