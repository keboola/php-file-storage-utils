<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Path;

use Keboola\FileStorage\ProviderInterface;

/**
 * Common implementation for ABS and S3
 */
class RelativePath implements RelativePathInterface
{
    /**
     * container for ABS, bucket for S3
     *
     * @var string
     */
    private $root;

    /** @var string */
    private $path;

    /** @var string */
    private $fileName;

    /** @var ProviderInterface */
    private $provider;

    private function __construct(ProviderInterface $provider, string $root, string $path, string $fileName)
    {
        $this->root = $root;
        $this->path = $path;
        $this->fileName = $fileName;
        $this->provider = $provider;
    }

    public static function create(ProviderInterface $provider, string $root, string $path, string $fileName): self
    {
        // remove slash if on begging or end of path
        $path = trim($path, '/');
        return new self($provider, $root, $path, $fileName);
    }

    public static function createFromRelativePath(ProviderInterface $provider, string $relativePath): self
    {
        // remove slash if on begging of path
        $relativePath = ltrim($relativePath, '/');
        [$root, $path] = explode('/', $relativePath, 2);
        [$path, $fileName] = self::splitPath($path);

        return new self($provider, $root, $path, $fileName);
    }

    /**
     * @return array{string,string}
     */
    private static function splitPath(string $path): array
    {
        // split string by slash to path and fileName
        if (preg_match('/^(.*)\/([^\/]*)$/', $path, $matches)) {
            [/**full match*/, $path, $fileName] = $matches;
        } else {
            $fileName = $path;
            $path = '';
        }

        return [$path, $fileName];
    }

    public static function createFromRootAndPath(ProviderInterface $provider, string $root, string $path): self
    {
        // remove slash if on begging of path
        $path = ltrim($path, '/');
        [$path, $fileName] = self::splitPath($path);

        return new self($provider, $root, $path, $fileName);
    }

    /**
     * @inheritDoc
     */
    public function getPathname(): string
    {
        $path = $this->getRoot();

        if ($this->path) {
            $path .= '/' . $this->path;
        }

        $path .= '/' . $this->getFileName();

        return $path;
    }

    /**
     * @inheritDoc
     */
    public function getRoot(): string
    {
        return $this->root;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getProvider(): ProviderInterface
    {
        return $this->provider;
    }

    public function getPathnameWithoutRoot(): string
    {
        $path = '';

        if ($this->path) {
            $path .= $this->path . '/';
        }

        $path .= $this->getFileName();

        return $path;
    }

    public function getPathWithoutRoot(): string
    {
        // phpcs:ignore
        return $this->path ?? '';
    }

    public function getPath(): string
    {
        $path = $this->getRoot();

        if ($this->path) {
            $path .= '/' . $this->path;
        }

        return $path;
    }
}
