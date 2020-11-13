<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Functional\Abs;

use Keboola\FileStorage\Abs\ClientFactory;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use PHPUnit\Framework\TestCase;

class ClientFactoryTest extends TestCase
{
    private const CONTAINER = 'ping';

    /** @var BlobRestProxy */
    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->getClient();
        $this->client->deleteContainer(self::CONTAINER);
    }

    private function getClient(): BlobRestProxy
    {
        $connectionString = sprintf(
            'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s',
            (string) getenv('ABS_ACCOUNT_NAME'),
            (string) getenv('ABS_ACCOUNT_KEY')
        );

        return ClientFactory::createClientFromConnectionString($connectionString);
    }

    public function testClient(): void
    {
        $this->client->createContainer(self::CONTAINER);
        $this->client->deleteContainer(self::CONTAINER);

        $this->expectNotToPerformAssertions();
    }
}
