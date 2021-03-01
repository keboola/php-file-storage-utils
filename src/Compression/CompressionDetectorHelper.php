<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Compression;

final class CompressionDetectorHelper
{
    public static function isGzipped(string $filePath): bool
    {
        return in_array(strtolower(pathinfo($filePath, PATHINFO_EXTENSION)), [
            'gz',
            'gzip',
        ]);
    }
}
