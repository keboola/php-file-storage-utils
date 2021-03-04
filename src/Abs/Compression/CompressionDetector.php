<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Abs\Compression;

use finfo;
use Keboola\FileStorage\FileNotFoundException;
use Keboola\FileStorage\Path\RelativePathInterface;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\GetBlobOptions;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Common\Models\Range;
use const FILEINFO_MIME;

final class CompressionDetector
{
    private const ERR_CODE_BLOB_NOT_FOUND = 404;
    private const ERR_CODE_INVALID_PAGE_RANGE = 416;
    private const BYTES_RANGE_START = 0;
    private const BYTES_TO_DOWNLOAD = 24;

    /** @var BlobRestProxy */
    private $blobClient;

    public function __construct(BlobRestProxy $blobClient)
    {
        $this->blobClient = $blobClient;
    }

    public function isGzip(RelativePathInterface $path): bool
    {
        $options = new GetBlobOptions();
        $options->setRange(new Range(self::BYTES_RANGE_START, self::BYTES_TO_DOWNLOAD));
        try {
            $result = $this->blobClient->getBlob(
                $path->getRoot(),
                $path->getPathnameWithoutRoot()
            );
        } catch (ServiceException $e) {
            if ($e->getCode() === self::ERR_CODE_BLOB_NOT_FOUND) {
                throw new FileNotFoundException($path, $e);
            }
            if ($e->getCode() === self::ERR_CODE_INVALID_PAGE_RANGE) {
                // file is empty
                return false;
            }
            throw $e;
        }
        $fileInfo = new finfo(FILEINFO_MIME);
        $mimeType = $fileInfo->buffer((string) fgets($result->getContentStream()));

        if ($mimeType === false) {
            return false;
        }

        return strpos($mimeType, 'gzip') !== false;
    }
}
