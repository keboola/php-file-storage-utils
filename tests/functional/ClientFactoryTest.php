<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Functional\Abs;

use Keboola\FileStorage\Tests\Common\BaseFunctionalTestCase;

class ClientFactoryTest extends BaseFunctionalTestCase
{
    private const CONTAINER = 'ping';

    protected function setUp(): void
    {
        parent::setUp();
        try {
            $this->client->deleteContainer(self::CONTAINER);
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function testClient(): void
    {
        $this->client->createContainer(self::CONTAINER);
        $this->client->deleteContainer(self::CONTAINER);

        $this->expectNotToPerformAssertions();
    }
}
