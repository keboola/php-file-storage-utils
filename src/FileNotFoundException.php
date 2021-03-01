<?php

declare(strict_types=1);

namespace Keboola\FileStorage;

use Exception;
use Keboola\FileStorage\Path\RelativePathInterface;
use Throwable;

class FileNotFoundException extends Exception
{
    public function __construct(
        RelativePathInterface $relativePath,
        Throwable $e
    ) {
        parent::__construct(
            sprintf('File "%s" was not found.', $relativePath->getPathname()),
            0,
            $e
        );
    }
}
