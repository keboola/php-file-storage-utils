<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Abs;

use GuzzleHttp\Exception\RequestException;
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

    protected function onRequest(RequestInterface $request): RequestInterface
    {
        $this->logger->debug(sprintf('Request: %s', (string) $request->getUri()));
        return $request;
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
                sprintf('Request OK: %s', (string) $request->getUri()),
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
            $reasonArr = [];
            if ($reason instanceof \Throwable) {
                $reasonArr = [
                    'message' => $reason->getMessage(),
                    'code' => $reason->getCode(),
                    'trace' => $reason->getTraceAsString(),
                ];
            }

            if ($reason instanceof RequestException) {
                $response = null;
                if ($reason->getResponse() !== null) {
                    $response = [
                        'body' => (string) $reason->getResponse()->getBody(),
                        'headers' => $reason->getResponse()->getHeaders(),
                    ];
                }

                $reasonArr = [
                    'message' => $reason->getMessage(),
                    'response' => $response,
                    'code' => $reason->getCode(),
                    'trace' => $reason->getTraceAsString(),
                ];
            }
            $this->logger->info(
                sprintf('Request REJECT: %s', (string) $request->getUri()),
                [
                    'request' => [
                        'method' => $request->getMethod(),
                        'body' => (string) $request->getBody(),
                        'headers' => $request->getHeaders(),
                    ],
                    'reason' => $reasonArr,
                    'options' => $options,
                ]
            );
            return new RejectedPromise($reason);
        };
    }
}
