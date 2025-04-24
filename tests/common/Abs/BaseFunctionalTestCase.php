<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Common\Abs;

use Keboola\FileStorage\Abs\ClientFactory;
use Keboola\FileStorage\Tests\Common\EchoLogger;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use PHPUnit\Framework\TestCase;

class BaseFunctionalTestCase extends TestCase
{
    /** @var BlobRestProxy */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->getClient();
    }

    private function getClient(): BlobRestProxy
    {
        $connectionString = sprintf(
            'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s',
            (string) getenv('ABS_ACCOUNT_NAME'),
            (string) getenv('ABS_ACCOUNT_KEY'),
        );

        return ClientFactory::createClientFromConnectionString($connectionString, new EchoLogger());
    }
}
