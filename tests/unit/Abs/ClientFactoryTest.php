<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Unit\Abs;

use Keboola\FileStorage\Abs\ClientFactory;
use Keboola\FileStorage\Abs\LoggerMiddleware;
use MicrosoftAzure\Storage\Common\Middlewares\RetryMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class ClientFactoryTest extends TestCase
{
    public function testCreateFromConnectionString(): void
    {
        $client = ClientFactory::createClientFromConnectionString(
            'DefaultEndpointsProtocol=https;AccountName=xxx;AccountKey=ZHNhZGFzZGE=;EndpointSuffix=core.windows.net',
        );
        $hasRetryMiddleware = false;
        $hasLoggerMiddleware = false;
        foreach ($client->getMiddlewares() as $middleware) {
            if ($middleware instanceof RetryMiddleware) {
                $hasRetryMiddleware = true;
            }
            if ($middleware instanceof LoggerMiddleware) {
                $hasLoggerMiddleware = true;
            }
        }
        $this->assertTrue($hasRetryMiddleware);
        $this->assertFalse($hasLoggerMiddleware);
    }

    public function testCreateFromConnectionStringWithLogger(): void
    {
        $client = ClientFactory::createClientFromConnectionString(
            'DefaultEndpointsProtocol=https;AccountName=xxx;AccountKey=ZHNhZGFzZGE=;EndpointSuffix=core.windows.net',
            $this->getLogger(),
        );
        $hasRetryMiddleware = false;
        $hasLoggerMiddleware = false;
        foreach ($client->getMiddlewares() as $middleware) {
            if ($middleware instanceof RetryMiddleware) {
                $hasRetryMiddleware = true;
            }
            if ($middleware instanceof LoggerMiddleware) {
                $hasLoggerMiddleware = true;
            }
        }
        $this->assertTrue($hasRetryMiddleware);
        $this->assertTrue($hasLoggerMiddleware);
    }

    private function getLogger(): LoggerInterface
    {
        return new class extends AbstractLogger {
            // phpcs:disable
            public function log($level, $message, array $context = []): void
            {
            }
            // phpcs:enable
        };
    }
}
