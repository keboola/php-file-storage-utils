<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Functional\Abs\LineEnding;

use Generator;
use Keboola\FileStorage\Abs\LineEnding\LineEndingDetector;
use Keboola\FileStorage\LineEnding\StringLineEndingDetectorHelper;
use Keboola\FileStorage\Tests\Common\Abs\ContainerFunctionalTestCase;

class LineEndingDetectorTest extends ContainerFunctionalTestCase
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
    public function testGetLineEnding(string $file, string $expectedLineEnding): void
    {
        $file = $this->uploadFile(__DIR__ . '/../../../unit/LineEnding/stub/' . $file);
        $detector = LineEndingDetector::createForClient($this->client);
        $result = $detector->getLineEnding($file);

        self::assertEquals($expectedLineEnding, $result);
    }
}
