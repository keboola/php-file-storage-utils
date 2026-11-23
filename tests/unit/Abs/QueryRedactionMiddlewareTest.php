<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Unit\Abs;

use Generator;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Keboola\FileStorage\Abs\ClientFactory;
use Keboola\FileStorage\Abs\QueryRedactionMiddleware;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use RuntimeException;
use Throwable;

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

    public function testRequestExceptionWithoutResponseIsRedacted(): void
    {
        $request = self::request();
        $reason = new RequestException(
            sprintf('cURL error 56: Recv failure for %s', (string) $request->getUri()),
            $request,
        );

        $thrown = self::rejectThrough($reason, $request);

        $this->assertSame(RequestException::class, $thrown::class);
        $this->assertInstanceOf(RequestException::class, $thrown);
        $this->assertNull($thrown->getResponse());
        $this->assertStringNotContainsString(self::MARKER, $thrown->getMessage());
        $this->assertSame('', $thrown->getRequest()->getUri()->getQuery());
        $this->assertNull($thrown->getPrevious());
    }

    public function testClientExceptionKeepsClassCodeAndResponse(): void
    {
        $request = self::request();
        $response = new Response(403, [], 'AuthenticationFailed');
        $reason = new ClientException(
            sprintf('Client error: `GET %s` resulted in a `403 Forbidden`', (string) $request->getUri()),
            $request,
            $response,
        );

        $thrown = self::rejectThrough($reason, $request);

        $this->assertSame(ClientException::class, $thrown::class);
        $this->assertInstanceOf(ClientException::class, $thrown);
        $this->assertSame(403, $thrown->getCode());
        $this->assertSame($response, $thrown->getResponse());
        $this->assertStringNotContainsString(self::MARKER, $thrown->getMessage());
        $this->assertSame('', $thrown->getRequest()->getUri()->getQuery());
    }

    public function testDecodedSignatureIsRedacted(): void
    {
        // The signature travels encoded, but the message may carry it decoded or reordered.
        $request = new Request(
            'GET',
            'http://127.0.0.1:1/container/blob?sig=' . self::MARKER . '%2Fab%2Bcd&sv=2020-08-04',
        );
        $reason = new RequestException(
            'Request failed with sig=' . self::MARKER . '/ab+cd and sv=2020-08-04',
            $request,
        );

        $thrown = self::rejectThrough($reason, $request);

        $this->assertStringNotContainsString(self::MARKER . '/ab+cd', $thrown->getMessage());
        $this->assertStringNotContainsString(self::MARKER . '%2Fab%2Bcd', $thrown->getMessage());
    }

    public function testClientFactoryPushesMiddlewareLast(): void
    {
        $client = ClientFactory::createClientFromConnectionString(self::CONNECTION_STRING);

        $middlewares = [];
        foreach ($client->getMiddlewares() as $middleware) {
            $middlewares[] = $middleware;
        }

        $this->assertNotSame([], $middlewares);
        // It must sit closest to the handler, otherwise the middlewares above it still see the SAS.
        $this->assertInstanceOf(QueryRedactionMiddleware::class, $middlewares[count($middlewares) - 1]);
    }

    private static function request(): RequestInterface
    {
        return new Request(
            'GET',
            'http://127.0.0.1:1/container/blob?sv=2020-08-04&sig=' . self::MARKER,
        );
    }

    /**
     * Runs one rejection through the middleware and returns what the caller would catch.
     */
    private static function rejectThrough(Throwable $reason, RequestInterface $request): Throwable
    {
        $middleware = new QueryRedactionMiddleware();
        $handler = $middleware(
            static fn (mixed ...$args): PromiseInterface => new RejectedPromise($reason),
        );

        try {
            $handler($request, [])->wait();
        } catch (Throwable $thrown) {
            return $thrown;
        }

        throw new RuntimeException('The promise must be rejected.');
    }
}
