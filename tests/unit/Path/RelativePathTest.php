<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Unit\Path;

use Keboola\FileStorage\Path\RelativePath;
use Keboola\FileStorage\S3\S3Provider;
use PHPUnit\Framework\TestCase;

class RelativePathTest extends TestCase
{
    public function testCreate(): void
    {
        $path = RelativePath::create(new S3Provider(), 'bucket', 'path/files/something', 'file.xxx');

        self::assertEquals('path/files/something', $path->getPathWithoutRoot());
        self::assertEquals('path/files/something/file.xxx', $path->getPathnameWithoutRoot());
        self::assertEquals('bucket/path/files/something/file.xxx', $path->getPathname());
        self::assertEquals('bucket/path/files/something', $path->getPath());
        self::assertEquals('bucket', $path->getRoot());
        self::assertInstanceOf(S3Provider::class, $path->getProvider());
        self::assertEquals('file.xxx', $path->getFileName());
    }

    public function testCreateFromRelativePath(): void
    {
        $path = RelativePath::createFromRelativePath(new S3Provider(), 'bucket/path/files/something/file.xxx');

        self::assertEquals('path/files/something', $path->getPathWithoutRoot());
        self::assertEquals('path/files/something/file.xxx', $path->getPathnameWithoutRoot());
        self::assertEquals('bucket/path/files/something/file.xxx', $path->getPathname());
        self::assertEquals('bucket/path/files/something', $path->getPath());
        self::assertEquals('bucket', $path->getRoot());
        self::assertInstanceOf(S3Provider::class, $path->getProvider());
        self::assertEquals('file.xxx', $path->getFileName());
    }

    public function testCreateFromRelativePathFileInRoot(): void
    {
        $path = RelativePath::createFromRelativePath(new S3Provider(), 'bucket/file.xxx');

        self::assertEquals('', $path->getPathWithoutRoot());
        self::assertEquals('file.xxx', $path->getPathnameWithoutRoot());
        self::assertEquals('bucket/file.xxx', $path->getPathname());
        self::assertEquals('bucket', $path->getPath());
        self::assertEquals('bucket', $path->getRoot());
        self::assertInstanceOf(S3Provider::class, $path->getProvider());
        self::assertEquals('file.xxx', $path->getFileName());
    }

    public function testCreateFromRootAndPath(): void
    {
        $path = RelativePath::createFromRootAndPath(new S3Provider(), 'bucket', 'path/files/something/file.xxx');

        self::assertEquals('path/files/something', $path->getPathWithoutRoot());
        self::assertEquals('path/files/something/file.xxx', $path->getPathnameWithoutRoot());
        self::assertEquals('bucket/path/files/something/file.xxx', $path->getPathname());
        self::assertEquals('bucket/path/files/something', $path->getPath());
        self::assertEquals('bucket', $path->getRoot());
        self::assertInstanceOf(S3Provider::class, $path->getProvider());
        self::assertEquals('file.xxx', $path->getFileName());
    }

    public function testCreateFromRootAndPathFileInRoot(): void
    {
        $path = RelativePath::createFromRootAndPath(new S3Provider(), 'bucket', 'file.xxx');

        self::assertEquals('', $path->getPathWithoutRoot());
        self::assertEquals('file.xxx', $path->getPathnameWithoutRoot());
        self::assertEquals('bucket/file.xxx', $path->getPathname());
        self::assertEquals('bucket', $path->getPath());
        self::assertEquals('bucket', $path->getRoot());
        self::assertInstanceOf(S3Provider::class, $path->getProvider());
        self::assertEquals('file.xxx', $path->getFileName());
    }

    public function testCreateFromRootAndPathStripSlash(): void
    {
        $path = RelativePath::createFromRootAndPath(new S3Provider(), 'bucket', '/path/files/something/file.xxx');

        self::assertEquals('path/files/something', $path->getPathWithoutRoot());
        self::assertEquals('path/files/something/file.xxx', $path->getPathnameWithoutRoot());
        self::assertEquals('bucket/path/files/something/file.xxx', $path->getPathname());
        self::assertEquals('bucket/path/files/something', $path->getPath());
        self::assertEquals('bucket', $path->getRoot());
        self::assertInstanceOf(S3Provider::class, $path->getProvider());
        self::assertEquals('file.xxx', $path->getFileName());
    }

    public function testCreateStripSlash(): void
    {
        $path = RelativePath::create(new S3Provider(), 'bucket', '/path/files/something/', 'file.xxx');

        self::assertEquals('path/files/something', $path->getPathWithoutRoot());
        self::assertEquals('path/files/something/file.xxx', $path->getPathnameWithoutRoot());
        self::assertEquals('bucket/path/files/something/file.xxx', $path->getPathname());
        self::assertEquals('bucket/path/files/something', $path->getPath());
        self::assertEquals('bucket', $path->getRoot());
        self::assertInstanceOf(S3Provider::class, $path->getProvider());
        self::assertEquals('file.xxx', $path->getFileName());
    }
}
