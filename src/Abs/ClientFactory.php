<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Abs;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;

final class ClientFactory
{
    public static function createClientFromConnectionString(string $connectionString): BlobRestProxy
    {
        $client = BlobRestProxy::createBlobService($connectionString);
        $client->pushMiddleware(RetryMiddlewareFactory::create());

        return $client;
    }
}
