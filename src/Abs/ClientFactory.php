<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Abs;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use Psr\Log\LoggerInterface;

final class ClientFactory
{
    public static function createClientFromConnectionString(
        string $connectionString,
        ?LoggerInterface $logger = null,
    ): BlobRestProxy {
        $client = BlobRestProxy::createBlobService($connectionString, [
            'http' => [
                'connect_timeout' => 10,
                'timeout' => 120,
            ],
        ]);
        $client->pushMiddleware(RetryMiddlewareFactory::create());
        if ($logger !== null) {
            $client->pushMiddleware(new LoggerMiddleware($logger));
        }
        // Pushed last, so it sits closest to Guzzle and the retry and logger see the redacted failure.
        $client->pushMiddleware(new QueryRedactionMiddleware());

        return $client;
    }
}
