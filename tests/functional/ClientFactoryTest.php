<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Functional\Abs;

use Keboola\FileStorage\Abs\ClientFactory;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use PHPUnit\Framework\TestCase;

class ClientFactoryTest extends TestCase
{
    public function testClient(): void
    {
        $client = $this->getClient();

        $client->createContainer('ping');
        $client->deleteContainer('ping');

        $this->expectNotToPerformAssertions();
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
}
