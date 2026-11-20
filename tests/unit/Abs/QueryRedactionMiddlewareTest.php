<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Unit\Abs;

use Generator;
use GuzzleHttp\Exception\ConnectException;
use Keboola\FileStorage\Abs\ClientFactory;
use Keboola\FileStorage\Abs\QueryRedactionMiddleware;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use PHPUnit\Framework\TestCase;

class QueryRedactionMiddlewareTest extends TestCase
{
    private const MARKER = 'LEAKMARKER';

    // Nothing listens on port 1, so the request fails without a response.
    private const CONNECTION_STRING = 'BlobEndpoint=http://127.0.0.1:1;'
        . 'SharedAccessSignature=sv=2020-08-04&sig=' . self::MARKER;

    /**
     * @dataProvider operationProvider
     * @param callable(BlobRestProxy): mixed $operation
     */
    public function testFailedRequestDoesNotCarryQueryString(callable $operation): void
    {
        $client = BlobRestProxy::createBlobService(self::CONNECTION_STRING, [
            'http' => [
                'connect_timeout' => 1,
            ],
        ]);
        $client->pushMiddleware(new QueryRedactionMiddleware());

        try {
            $operation($client);
            $this->fail('The request must fail.');
        } catch (ConnectException $e) {
            $this->assertSame(ConnectException::class, $e::class);
            $this->assertStringNotContainsString(self::MARKER, $e->getMessage());
            $this->assertStringNotContainsString(
                self::MARKER,
                (string) json_encode($e->getHandlerContext(), JSON_THROW_ON_ERROR),
            );
            $this->assertStringNotContainsString(self::MARKER, (string) $e->getRequest()->getUri());
            $this->assertSame('', $e->getRequest()->getUri()->getQuery());
            $this->assertStringNotContainsString(self::MARKER, (string) $e);
            $this->assertNull($e->getPrevious());
        }
    }

    public function operationProvider(): Generator
    {
        yield 'getBlob' => [
            static fn (BlobRestProxy $client) => $client->getBlob('container', 'blob'),
        ];
        yield 'createBlockBlob' => [
            static fn (BlobRestProxy $client) => $client->createBlockBlob('container', 'blob', 'content'),
        ];
    }

    public function testClientFactoryPushesMiddleware(): void
    {
        $client = ClientFactory::createClientFromConnectionString(self::CONNECTION_STRING);

        $found = false;
        foreach ($client->getMiddlewares() as $middleware) {
            if ($middleware instanceof QueryRedactionMiddleware) {
                $found = true;
            }
        }
        $this->assertTrue($found);
    }
}
