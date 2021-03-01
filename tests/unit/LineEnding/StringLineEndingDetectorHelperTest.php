<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Unit\LineEnding;

use Generator;
use Keboola\FileStorage\LineEnding\StringLineEndingDetectorHelper;
use PHPUnit\Framework\TestCase;

class StringLineEndingDetectorHelperTest extends TestCase
{
    /**
     * @return Generator<string, array{string, string}>
     */
    public function lineEndingsProvider(): Generator
    {
        yield 'CRLF' => [
            'crlf.txt',
            StringLineEndingDetectorHelper::EOL_WINDOWS,
        ];

        yield 'LF' => [
            'lf.txt',
            StringLineEndingDetectorHelper::EOL_UNIX,
        ];

        yield 'no terminator' => [
            'no-terminator.txt',
            StringLineEndingDetectorHelper::EOL_UNIX,
        ];

        yield 'exotic terminator' => [
            'unknown.txt',
            StringLineEndingDetectorHelper::EOL_UNIX,
        ];
    }

    /**
     * @dataProvider lineEndingsProvider
     */
    public function testGetLineEndingFromString(string $file, string $expectedLineEnding): void
    {
        $fileContent = (string) file_get_contents(__DIR__ . '/stub/' . $file);
        $result = StringLineEndingDetectorHelper::getLineEndingFromString($fileContent);

        self::assertEquals($expectedLineEnding, $result);
    }
}
