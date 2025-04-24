<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Unit\Abs\Path;

use Keboola\FileStorage\Abs\AbsProvider;
use Keboola\FileStorage\Abs\Path\AbsAbsolutePath;
use Keboola\FileStorage\Path\RelativePath;
use PHPUnit\Framework\TestCase;

class AbsAbsolutePathTest extends TestCase
{
    public function testCreateFromRelativePath(): void
    {
        $relativePath = RelativePath::createFromRelativePath(
            new AbsProvider(),
            'permanent-8-snapshots-in-c-api-tests-languages/2072.csv.gz',
        );

        $path = AbsAbsolutePath::createFromRelativePath($relativePath, 'kbcAccount');

        $relativePath = $path->getRelativePath();

        self::assertEquals('', $relativePath->getPathWithoutRoot());
        self::assertEquals('2072.csv.gz', $relativePath->getPathnameWithoutRoot());
        self::assertEquals('permanent-8-snapshots-in-c-api-tests-languages/2072.csv.gz', $relativePath->getPathname());
        self::assertEquals('permanent-8-snapshots-in-c-api-tests-languages', $relativePath->getRoot());
        self::assertInstanceOf(AbsProvider::class, $relativePath->getProvider());
        self::assertEquals('2072.csv.gz', $relativePath->getFileName());

        self::assertInstanceOf(AbsProvider::class, $path->getProvider());
        self::assertEquals('kbcAccount', $path->getAccountName());
        self::assertEquals(
            'azure://kbcAccount.blob.core.windows.net/permanent-8-snapshots-in-c-api-tests-languages/2072.csv.gz',
            $path->getAbsoluteUrl(AbsAbsolutePath::PROTOCOL_AZURE),
        );
        self::assertEquals(
            'https://kbcAccount.blob.core.windows.net/permanent-8-snapshots-in-c-api-tests-languages/2072.csv.gz',
            $path->getAbsoluteUrl(AbsAbsolutePath::PROTOCOL_HTTPS),
        );
    }
}
