<?php

declare(strict_types=1);

namespace Keboola\FileStorage\LineEnding;

final class StringLineEndingDetectorHelper
{
    public const EOL_UNIX = 'lf';        // Code: \n
    public const EOL_TRS80 = 'cr';        // Code: \r
    public const EOL_ACORN = 'lfcr';      // Code: \n \r
    public const EOL_WINDOWS = 'crlf';      // Code: \r \n
    public const EOLS = [
        self::EOL_ACORN => "\n\r",  // 0x0A - 0x0D - acorn BBC
        self::EOL_WINDOWS => "\r\n",  // 0x0D - 0x0A - Windows, DOS OS/2
        self::EOL_UNIX => "\n",    // 0x0A -      - Unix, OSX
        self::EOL_TRS80 => "\r",    // 0x0D -      - Apple ][, TRS80
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
            throw new UnknownLineEndingException();
        }
        return $detectedConstantValue;
    }
}
