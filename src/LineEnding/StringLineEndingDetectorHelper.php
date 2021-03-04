<?php

declare(strict_types=1);

namespace Keboola\FileStorage\LineEnding;

final class StringLineEndingDetectorHelper
{
    public const EOL_UNIX = 'lf';        // Code: \n
    public const EOL_WINDOWS = 'crlf';      // Code: \r \n
    public const EOLS = [
        self::EOL_WINDOWS => "\r\n",  // 0x0D - 0x0A - Windows, DOS OS/2
        self::EOL_UNIX => "\n",    // 0x0A -      - Unix, OSX
    ];

    /**
     * @return self::EOL_*
     */
    public static function getLineEndingFromString(string $string): string
    {
        $cursorCount = 0;
        $detectedConstantValue = null;
        foreach (self::EOLS as $key => $eol) {
            $count = substr_count($string, $eol);
            if ($count > $cursorCount) {
                $cursorCount = $count;
                $detectedConstantValue = $key;
            }
        }
        if ($detectedConstantValue === null) {
            $detectedConstantValue = self::EOL_UNIX;
        }
        return $detectedConstantValue;
    }
}
