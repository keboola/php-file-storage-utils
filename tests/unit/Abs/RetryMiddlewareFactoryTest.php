<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Unit\Abs;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Keboola\FileStorage\Abs\RetryMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class RetryMiddlewareFactoryTest extends TestCase
{
    public function testCreateWithNegativeNumberOfRetries(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('should be positive number');
        RetryMiddlewareFactory::create(
            -1,
            RetryMiddlewareFactory::DEFAULT_RETRY_INTERVAL,
            RetryMiddlewareFactory::LINEAR_INTERVAL_ACCUMULATION
        );
    }

    public function testCreateWithNegativeInterval(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('should be positive number');
        RetryMiddlewareFactory::create(
            RetryMiddlewareFactory::DEFAULT_NUMBER_OF_RETRIES,
            -1,
            RetryMiddlewareFactory::LINEAR_INTERVAL_ACCUMULATION
        );
    }

    public function testCreateWithInvalidAccumulationMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('is invalid');
        RetryMiddlewareFactory::create(
            RetryMiddlewareFactory::DEFAULT_NUMBER_OF_RETRIES,
            RetryMiddlewareFactory::DEFAULT_RETRY_INTERVAL,
            'string that does not make sense'
        );
    }

    public function testCreateRetryDeciderWithGeneralRetryDecider(): void
    {
        $createRetryDecider = self::getMethod('createRetryDecider', new RetryMiddlewareFactory());
        $generalDecider = $createRetryDecider->invokeArgs(null, [3]);
        $request = new Request('PUT', '127.0.0.1');

        // retry
        $retryResult = $generalDecider(1, $request, new Response(408));
        $this->assertTrue($retryResult);

        $retryResult = $generalDecider(1, $request, new Response(503));
        $this->assertTrue($retryResult);

        $retryResult = $generalDecider(1, $request, null, new ConnectException('message', $request));
        $this->assertTrue($retryResult);

        $retryResult = $generalDecider(1, $request, null, new RequestException('message', $request));
        $this->assertTrue($retryResult);

        $retryResult = $generalDecider(1, $request, null, new Exception('message'));
        $this->assertTrue($retryResult);

        $retryResult = $generalDecider(1, $request, null, new ClientException('message', $request, new Response(500)));
        $this->assertTrue($retryResult);

        //no-retry
        $retryResult = $generalDecider(1, $request, new Response(501));
        $this->assertFalse($retryResult);

        $retryResult = $generalDecider(1, $request, new Response(505));
        $this->assertFalse($retryResult);

        $retryResult = $generalDecider(1, $request, new Response(200));
        $this->assertFalse($retryResult);

        $retryResult = $generalDecider(4, $request, new Response(503));
        $this->assertFalse($retryResult);

        $retryResult = $generalDecider(1, $request, null, new ClientException('message', $request, new Response(404)));
        $this->assertFalse($retryResult);
    }

    private static function getMethod(string $method, RetryMiddlewareFactory $class): ReflectionMethod
    {
        $class = new ReflectionClass($class);
        $method = $class->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }

    public function testCreateLinearDelayCalculator(): void
    {
        $creator = self::getMethod('createLinearDelayCalculator', new RetryMiddlewareFactory());
        $linearDelayCalculator = $creator->invokeArgs(null, [1000]);
        for ($index = 0; $index < 10; ++$index) {
            $this->assertEquals($index * 1000, $linearDelayCalculator($index));
        }
    }

    public function testCreateExponentialDelayCalculator(): void
    {
        $creator = self::getMethod('createExponentialDelayCalculator', new RetryMiddlewareFactory());
        $exponentialDelayCalculator = $creator->invokeArgs(null, [1000]);
        for ($index = 0; $index < 3; ++$index) {
            $pow = 2 ** $index;
            $this->assertEquals($pow * 1000, $exponentialDelayCalculator($index));
        }
    }
}
