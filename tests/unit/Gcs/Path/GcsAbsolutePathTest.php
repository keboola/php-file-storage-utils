<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Unit\Gcs\Path;

use Keboola\FileStorage\Gcs\GcsProvider;
use Keboola\FileStorage\Gcs\Path\GcsAbsolutePath;
use Keboola\FileStorage\Path\RelativePath;
use PHPUnit\Framework\TestCase;

class GcsAbsolutePathTest extends TestCase
{
    public function testCreateFromRelativePath(): void
    {
        $relativePath = RelativePath::createFromRelativePath(
            new GcsProvider(),
            '/bucket/permanent/8/snapshots/in/c-api-tests/languages/2072.csv.gz',
        );

        $path = GcsAbsolutePath::createFromRelativePath($relativePath);

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
        self::assertInstanceOf(GcsProvider::class, $relativePath->getProvider());
        self::assertEquals('2072.csv.gz', $relativePath->getFileName());

        self::assertInstanceOf(GcsProvider::class, $path->getProvider());
        self::assertEquals(
            'gs://bucket/permanent/8/snapshots/in/c-api-tests/languages/2072.csv.gz',
            $path->getAbsoluteUrl(GcsAbsolutePath::PROTOCOL_GS),
        );
        self::assertEquals(
            'gs://bucket/permanent/8/snapshots/in/c-api-tests/languages/2072.csv.gz',
            $path->getAbsoluteUrl(),
        );
    }
}
