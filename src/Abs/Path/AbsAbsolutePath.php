<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Abs\Path;

use Keboola\FileStorage\Path\AbsolutePathInterface;
use Keboola\FileStorage\Path\RelativePathInterface;
use Keboola\FileStorage\ProviderInterface;
use MicrosoftAzure\Storage\Common\Internal\Resources;

class AbsAbsolutePath implements AbsolutePathInterface
{
    public const PROTOCOL_AZURE = 'azure';
    public const PROTOCOL_HTTPS = 'https';

    /** @var RelativePathInterface */
    private $path;

    /** @var string */
    private $accountName;

    private function __construct(RelativePathInterface $path, string $accountName)
    {
        $this->path = $path;
        $this->accountName = $accountName;
    }

    public static function createFromRelativePath(RelativePathInterface $path, string $accountName): self
    {
        return new self($path, $accountName);
    }

    public function getAbsoluteUrl(string $protocol = self::PROTOCOL_AZURE): string
    {
        return sprintf(
            '%s://%s.%s/%s',
            $protocol,
            $this->accountName,
            Resources::BLOB_BASE_DNS_NAME,
            $this->path->getPathname(),
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

    public function getAccountName(): string
    {
        return $this->accountName;
    }
}
