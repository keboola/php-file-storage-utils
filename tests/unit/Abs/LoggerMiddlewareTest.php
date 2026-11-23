<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Abs;

use Exception;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionMethod;

class LoggerMiddlewareTest extends TestCase
{
    public function testOnRequest(): void
    {
        /** @var LoggerInterface|MockObject $logMock */
        $logMock = $this->createMock(LoggerInterface::class);
        $logMock->expects($this->once())->method('debug')->with('Request: http://www.keboola.com');

        $middleware = new LoggerMiddleware($logMock);
        $onRequest = self::getMethod('onRequest', $middleware);
        $request = new Request('GET', 'http://www.keboola.com');
        $onRequest->invokeArgs($middleware, [$request]);
    }

    private static function getMethod(string $method, LoggerMiddleware $class): ReflectionMethod
    {
        $class = new ReflectionClass($class);
        $method = $class->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }

    public function testOnFulfilled(): void
    {
        $request = new Request('GET', 'http://www.keboola.com');
        $response = new Response();

        /** @var LoggerInterface|MockObject $logMock */
        $logMock = $this->createMock(LoggerInterface::class);
        $logMock->expects($this->once())->method('debug')->with(
            'Request OK: http://www.keboola.com',
            [
                'request' => [
                    'method' => $request->getMethod(),
                    'body' => (string) $request->getBody(),
                    'headers' => $request->getHeaders(),
                ],
                'response' => [
                    'body' => (string) $request->getBody(),
                    'headers' => $request->getHeaders(),
                ],
                'options' => [],
            ],
        );

        $middleware = new LoggerMiddleware($logMock);
        $onFulfilled = self::getMethod('onFulfilled', $middleware);
        $request = new Request('GET', 'http://www.keboola.com');
        $callable = $onFulfilled->invokeArgs($middleware, [$request, []]);
        $callable($response);
    }

    public function testOnRejectedNoResponse(): void
    {
        $request = new Request('GET', 'http://www.keboola.com');
        $reason = new RequestException('test message', $request);

        /** @var LoggerInterface|MockObject $logMock */
        $logMock = $this->createMock(LoggerInterface::class);
        $logMock->expects($this->once())->method('info')->with(
            'Request REJECT: http://www.keboola.com',
            [
                'request' => [
                    'method' => $request->getMethod(),
                    'body' => (string) $request->getBody(),
                    'headers' => $request->getHeaders(),
                ],
                'reason' => [
                    'message' => $reason->getMessage(),
                    'response' => null,
                    'code' => $reason->getCode(),
                    'trace' => $reason->getTraceAsString(),
                ],
                'options' => [],
            ],
        );

        $middleware = new LoggerMiddleware($logMock);
        $onRejected = self::getMethod('onRejected', $middleware);
        $callable = $onRejected->invokeArgs($middleware, [$request, []]);
        $callable($reason);
    }

    public function testOnRejectedResponse(): void
    {
        $request = new Request('GET', 'http://www.keboola.com');
        $response = new Response(400, [], '{}');
        $reason = new RequestException('test message', $request, $response);

        /** @var LoggerInterface|MockObject $logMock */
        $logMock = $this->createMock(LoggerInterface::class);
        $logMock->expects($this->once())->method('info')->with(
            'Request REJECT: http://www.keboola.com',
            [
                'request' => [
                    'method' => $request->getMethod(),
                    'body' => (string) $request->getBody(),
                    'headers' => $request->getHeaders(),
                ],
                'reason' => [
                    'message' => $reason->getMessage(),
                    'response' => [
                        'body' => (string) $response->getBody(),
                        'headers' => $response->getHeaders(),
                    ],
                    'code' => $reason->getCode(),
                    'trace' => $reason->getTraceAsString(),
                ],
                'options' => [],
            ],
        );

        $middleware = new LoggerMiddleware($logMock);
        $onRejected = self::getMethod('onRejected', $middleware);
        $callable = $onRejected->invokeArgs($middleware, [$request, []]);
        $callable($reason);
    }

    public function testOnRejectedGeneralError(): void
    {
        $request = new Request('GET', 'http://www.keboola.com');
        $reason = new Exception('test message', 10);

        /** @var LoggerInterface|MockObject $logMock */
        $logMock = $this->createMock(LoggerInterface::class);
        $logMock->expects($this->once())->method('info')->with(
            'Request REJECT: http://www.keboola.com',
            [
                'request' => [
                    'method' => $request->getMethod(),
                    'body' => (string) $request->getBody(),
                    'headers' => $request->getHeaders(),
                ],
                'reason' => [
                    'message' => 'test message',
                    'code' => 10,
                    'trace' => $reason->getTraceAsString(),
                ],
                'options' => [],
            ],
        );

        $middleware = new LoggerMiddleware($logMock);
        $onRejected = self::getMethod('onRejected', $middleware);
        $callable = $onRejected->invokeArgs($middleware, [$request, []]);
        $callable($reason);
    }

    public function testSasAndAuthorizationHeaderAreNotLogged(): void
    {
        $request = new Request(
            'PUT',
            'https://account.blob.core.windows.net/container/blob?sv=2020-01-01&sig=SECRET%2Bsig',
            ['Authorization' => 'SharedKey account:SECRETHMAC', 'x-ms-version' => '2020-01-01'],
        );
        $expectedHeaders = [
            'Host' => ['account.blob.core.windows.net'],
            'Authorization' => ['***'],
            'x-ms-version' => ['2020-01-01'],
        ];
        $records = [];
        $collect = static function (string $message, array $context = []) use (&$records): void {
            $records[] = [$message, $context];
        };

        /** @var LoggerInterface|MockObject $logMock */
        $logMock = $this->createMock(LoggerInterface::class);
        $logMock->expects($this->exactly(2))->method('debug')->willReturnCallback($collect);
        $logMock->expects($this->once())->method('info')->willReturnCallback($collect);

        $middleware = new LoggerMiddleware($logMock);
        self::getMethod('onRequest', $middleware)->invokeArgs($middleware, [$request]);
        self::getMethod('onFulfilled', $middleware)->invokeArgs($middleware, [$request, []])(new Response());
        self::getMethod('onRejected', $middleware)->invokeArgs($middleware, [$request, []])(
            new Exception('test message'),
        );

        $url = 'https://account.blob.core.windows.net/container/blob';
        self::assertSame('Request: ' . $url, $records[0][0]);
        self::assertSame('Request OK: ' . $url, $records[1][0]);
        self::assertSame('Request REJECT: ' . $url, $records[2][0]);
        self::assertSame($expectedHeaders, $records[1][1]['request']['headers']);
        self::assertSame($expectedHeaders, $records[1][1]['response']['headers']);
        self::assertSame($expectedHeaders, $records[2][1]['request']['headers']);

        $logged = (string) json_encode($records);
        self::assertStringNotContainsString('SECRET', $logged);
        self::assertStringNotContainsString('sig=', $logged);
    }

    /**
     * @dataProvider provideReasonsNamingTheSignedUri
     */
    public function testSasIsNotLoggedInTheRejectReason(callable $createReason, string $expectedMessage): void
    {
        $request = new Request(
            'PUT',
            'https://account.blob.core.windows.net/container/blob?sv=2020-01-01&sig=SECRET%2Bsig',
        );
        $records = [];

        /** @var LoggerInterface|MockObject $logMock */
        $logMock = $this->createMock(LoggerInterface::class);
        $logMock->expects($this->once())->method('info')->willReturnCallback(
            static function (string $message, array $context = []) use (&$records): void {
                $records[] = [$message, $context];
            },
        );

        $middleware = new LoggerMiddleware($logMock);
        self::getMethod('onRejected', $middleware)->invokeArgs($middleware, [$request, []])($createReason($request));

        self::assertSame($expectedMessage, $records[0][1]['reason']['message']);
        $logged = (string) json_encode($records);
        self::assertStringNotContainsString('SECRET', $logged);
        self::assertStringNotContainsString('sig=', $logged);
    }

    /**
     * @return iterable<string, array{callable, string}>
     */
    public static function provideReasonsNamingTheSignedUri(): iterable
    {
        $url = 'https://account.blob.core.windows.net/container/blob';

        yield 'HTTP error built by Guzzle' => [
            static fn (Request $request) => RequestException::create($request, new Response(403)),
            'Client error: `PUT ' . $url . '` resulted in a `403 Forbidden` response',
        ];
        yield 'connection error naming the URL' => [
            static fn (Request $request) => new ConnectException(
                'cURL error 7: Failed to connect for ' . $request->getUri(),
                $request,
            ),
            'cURL error 7: Failed to connect for ' . $url,
        ];
        yield 'plain exception naming the URL' => [
            static fn (Request $request) => new Exception('Failed: ' . $request->getUri()),
            'Failed: ' . $url,
        ];
    }
}
