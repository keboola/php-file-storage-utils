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

    protected function uploadFile(string $filePath): RelativePathInterface
    {
        return $this->uploadString((string) file_get_contents($filePath));
    }

    protected function uploadString(string $stringToUpload): RelativePathInterface
    {
        $fileName = md5(uniqid('', true));
        $this->client->createBlockBlob(
            $this->getContainerName(),
            $fileName,
            $stringToUpload
        );

        return RelativePath::create(
            new AbsProvider(),
            $this->getContainerName(),
            '',
            $fileName
        );
    }
}
