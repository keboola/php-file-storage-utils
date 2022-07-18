<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Gcs\Path;

use Keboola\FileStorage\Path\AbsolutePathInterface;
use Keboola\FileStorage\Path\RelativePathInterface;
use Keboola\FileStorage\ProviderInterface;

class GcsAbsolutePath implements AbsolutePathInterface
{
    public const PROTOCOL_GS = 'gs';

    /** @var RelativePathInterface */
    private $path;

    private function __construct(RelativePathInterface $path)
    {
        $this->path = $path;
    }

    public static function createFromRelativePath(RelativePathInterface $path): self
    {
        return new self($path);
    }


    public function getAbsoluteUrl(string $protocol = self::PROTOCOL_GS): string
    {
        return sprintf(
            '%s://%s',
            $protocol,
            $this->path->getPathname()
        );
    }

    public function getProvider(): ProviderInterface
    {
        return $this->path->getProvider();
    }

    public function getRelativePath(): RelativePathInterface
    {
        return $this->path;
    }
}
