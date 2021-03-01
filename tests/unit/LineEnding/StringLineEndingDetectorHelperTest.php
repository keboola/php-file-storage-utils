<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Unit\LineEnding;

use Generator;
use Keboola\FileStorage\LineEnding\StringLineEndingDetectorHelper;
use Keboola\FileStorage\LineEnding\UnknownLineEndingException;
use PHPUnit\Framework\TestCase;

class StringLineEndingDetectorHelperTest extends TestCase
{
    /**
     * @return Generator<string, array{string, string}>
     */
    public function lineEndingsProvider(): Generator
    {
        yield 'CR' => [
            'cr.txt',
            StringLineEndingDetectorHelper::EOL_TRS80,
        ];

        yield 'CRLF' => [
            'crlf.txt',
            StringLineEndingDetectorHelper::EOL_WINDOWS,
        ];

        yield 'LF' => [
            'lf.txt',
            StringLineEndingDetectorHelper::EOL_UNIX,
        ];

        yield 'LFCR' => [
            'lfcr.txt',
            StringLineEndingDetectorHelper::EOL_ACORN,
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

    public function testGetLineEndingFromStringUnknownLineEnding(): void
    {
        $fileContent = (string) file_get_contents(__DIR__ . '/stub/unknown.txt');
        $this->expectException(UnknownLineEndingException::class);
        $this->expectExceptionMessage('Unknown line ending.');
        StringLineEndingDetectorHelper::getLineEndingFromString($fileContent);
    }
}
