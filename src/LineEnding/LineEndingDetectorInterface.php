<?php

declare(strict_types=1);

namespace Keboola\FileStorage\LineEnding;

use Keboola\FileStorage\Path\RelativePathInterface;

interface LineEndingDetectorInterface
{
    public const BYTES_RANGE_END = 655360;

    /**
     * @return StringLineEndingDetectorHelper::EOL_*
     */
    public function getLineEnding(
        RelativePathInterface $path,
        int $bytes = self::BYTES_RANGE_END
    ): string;
}
