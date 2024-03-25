<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Unit\S3\Path;

use Keboola\FileStorage\Path\RelativePath;
use Keboola\FileStorage\S3\Path\S3AbsolutePath;
use Keboola\FileStorage\S3\S3Provider;
use PHPUnit\Framework\TestCase;

class S3AbsolutePathTest extends TestCase
{
    public function testCreateFromRelativePath(): void
    {
        $relativePath = RelativePath::createFromRelativePath(
            new S3Provider(),
            '/bucket/permanent/8/snapshots/in/c-api-tests/languages/2072.csv.gz',
        );

        $path = S3AbsolutePath::createFromRelativePath($relativePath);

        $relativePath = $path->getRelativePath();

        self::assertEquals('permanent/8/snapshots/in/c-api-tests/languages', $relativePath->getPathWithoutRoot());
        self::assertEquals(
            'permanent/8/snapshots/in/c-api-tests/languages/2072.csv.gz',
            $relativePath->getPathnameWithoutRoot(),
        );
        self::assertEquals(
            'bucket/permanent/8/snapshots/in/c-api-tests/languages/2072.csv.gz',
            $relativePath->getPathname(),
        );
        self::assertEquals('bucket', $relativePath->getRoot());
        self::assertInstanceOf(S3Provider::class, $relativePath->getProvider());
        self::assertEquals('2072.csv.gz', $relativePath->getFileName());

        self::assertInstanceOf(S3Provider::class, $path->getProvider());
        self::assertEquals(
            's3://bucket/permanent/8/snapshots/in/c-api-tests/languages/2072.csv.gz',
            $path->getAbsoluteUrl(S3AbsolutePath::PROTOCOL_S3),
        );
        self::assertEquals(
            's3://bucket/permanent/8/snapshots/in/c-api-tests/languages/2072.csv.gz',
            $path->getAbsoluteUrl(),
        );
    }
}
