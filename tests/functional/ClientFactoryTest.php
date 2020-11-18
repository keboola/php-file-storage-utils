<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Functional\Abs;

use Keboola\FileStorage\Tests\Common\BaseFunctionalTestCase;

class ClientFactoryTest extends BaseFunctionalTestCase
{
    private const CONTAINER = 'ping';

    /** @var string|null */
    private static $containerName = null;

    protected function setUp(): void
    {
        parent::setUp();
        try {
            $this->client->deleteContainer(self::getContainerName());
        } catch (\Throwable $e) {
            // ignore
        }
    }

    private static function getContainerName(): string
    {
        if (self::$containerName === null) {
            self::$containerName = self::CONTAINER . md5(uniqid('', true));
        }
        return self::$containerName;
    }

    public function testClient(): void
    {
        $this->client->createContainer(self::getContainerName());
        $this->client->deleteContainer(self::getContainerName());

        $this->expectNotToPerformAssertions();
    }
}
