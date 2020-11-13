<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Unit\Abs;

use Keboola\FileStorage\Abs\ClientFactory;
use MicrosoftAzure\Storage\Common\Middlewares\RetryMiddleware;
use PHPUnit\Framework\TestCase;

class ClientFactoryTest extends TestCase
{
    public function testCreateFromConnectionString(): void
    {
        $client = ClientFactory::createClientFromConnectionString(
            'DefaultEndpointsProtocol=https;AccountName=xxx;AccountKey=ZHNhZGFzZGE=;EndpointSuffix=core.windows.net'
        );
        $hasRetryMiddleware = false;
        foreach ($client->getMiddlewares() as $middleware) {
            if ($middleware instanceof RetryMiddleware) {
                $hasRetryMiddleware = true;
            }
        }
        $this->assertTrue($hasRetryMiddleware);
    }
}
