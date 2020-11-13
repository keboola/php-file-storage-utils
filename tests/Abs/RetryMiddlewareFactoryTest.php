<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Abs;

use Keboola\FileStorage\Abs\RetryMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;

class RetryMiddlewareFactoryTest extends TestCase
{
    public function testCreateWithNegativeNumberOfRetries(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('should be positive number');
        RetryMiddlewareFactory::create(
            -1,
            RetryMiddlewareFactory::DEFAULT_RETRY_INTERVAL,
            RetryMiddlewareFactory::LINEAR_INTERVAL_ACCUMULATION
        );
    }

    public function testCreateWithNegativeInterval(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('should be positive number');
        RetryMiddlewareFactory::create(
            RetryMiddlewareFactory::DEFAULT_NUMBER_OF_RETRIES,
            -1,
            RetryMiddlewareFactory::LINEAR_INTERVAL_ACCUMULATION
        );
    }

    public function testCreateWithInvalidAccumulationMethod(): void
    {
        $this->expectException(\InvalidArgumentException::class);
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
        $retryResult_1 = $generalDecider(1, $request, new Response(408));//retry
        $retryResult_2 = $generalDecider(1, $request, new Response(501));//no-retry
        $retryResult_3 = $generalDecider(1, $request, new Response(505));//no-retry
        $retryResult_4 = $generalDecider(1, $request, new Response(200));//no-retry
        $retryResult_5 = $generalDecider(1, $request, new Response(503));//retry
        $retryResult_6 = $generalDecider(4, $request, new Response(503));//no-retry
        $retryResult_7 = $generalDecider(1, $request, null, new ConnectException('message', $request)); //retry
        $retryResult_8 = $generalDecider(1, $request, null, new RequestException('message', $request)); //retry
        $retryResult_9 = $generalDecider(1, $request, null, new \Exception('message')); //retry

        //assert
        $this->assertTrue($retryResult_1);
        $this->assertFalse($retryResult_2);
        $this->assertFalse($retryResult_3);
        $this->assertFalse($retryResult_4);
        $this->assertTrue($retryResult_5);
        $this->assertFalse($retryResult_6);
        $this->assertTrue($retryResult_7);
        $this->assertTrue($retryResult_8);
        $this->assertTrue($retryResult_9);
    }

    private static function getMethod(string $method, RetryMiddlewareFactory $class): \ReflectionMethod
    {
        $class = new \ReflectionClass($class);
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
