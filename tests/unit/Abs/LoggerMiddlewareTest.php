<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Abs;

use Exception;
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
}
