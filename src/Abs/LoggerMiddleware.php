<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Abs;

use MicrosoftAzure\Storage\Common\Middlewares\MiddlewareBase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use Psr\Log\LoggerInterface;

class LoggerMiddleware extends MiddlewareBase
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * This function will be invoked after the request is sent, if
     * the promise is fulfilled.
     *
     * @param array<mixed> $options
     */
    protected function onFulfilled(RequestInterface $request, array $options): callable
    {
        return function (ResponseInterface $response) use (
            $request,
            $options
        ) {
            $this->logger->debug(
                sprintf('Request: %s', (string) $request->getUri()),
                [
                    'request' => $request,
                    'response' => $response,
                    'options' => $options,
                ]
            );
            return $response;
        };
    }

    /**
     * This function will be executed after the request is sent, if
     * the promise is rejected.
     *
     * @param array<mixed> $options
     */
    protected function onRejected(RequestInterface $request, array $options): callable
    {
        return function ($reason) use (
            $request,
            $options
        ) {
            $this->logger->info(
                sprintf('Request: %s', (string) $request->getUri()),
                [
                    'reason' => $reason,
                    'request' => $request,
                    'options' => $options,
                ]
            );
            return new RejectedPromise($reason);
        };
    }
}
