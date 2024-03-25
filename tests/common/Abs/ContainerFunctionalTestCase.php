<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Common\Abs;

use Keboola\FileStorage\Abs\AbsProvider;
use Keboola\FileStorage\Path\RelativePath;
use Keboola\FileStorage\Path\RelativePathInterface;
use Throwable;

class ContainerFunctionalTestCase extends BaseFunctionalTestCase
{
    /** @var string|null */
    private $containerName = null;

    protected function setUp(): void
    {
        parent::setUp();
        try {
            $this->client->deleteContainer($this->getContainerName());
        } catch (Throwable $e) {
            // ignore
        }

        $this->client->createContainer($this->getContainerName());
    }

    protected function getContainerName(): string
    {
        if ($this->containerName === null) {
            $this->containerName = md5((string) $this->getName());
        }
        return $this->containerName;
    }

    protected function uploadFile(string $filePath, bool $gzip = false, ?string $fileName = null): RelativePathInterface
    {
        return $this->uploadString((string) file_get_contents($filePath), $gzip, $fileName);
    }

    protected function uploadString(
        string $stringToUpload,
        bool $gzip = false,
        ?string $fileName = null,
    ): RelativePathInterface {
        if ($fileName === null) {
            $fileName = md5(uniqid('', true));
            if ($gzip) {
                $fileName .= '.gz';
            }
        }
        $this->client->createBlockBlob(
            $this->getContainerName(),
            $fileName,
            $stringToUpload,
        );

        return RelativePath::create(
            new AbsProvider(),
            $this->getContainerName(),
            '',
            $fileName,
        );
    }
}
