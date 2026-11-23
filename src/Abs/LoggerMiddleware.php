<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Abs;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\RejectedPromise;
use MicrosoftAzure\Storage\Common\Middlewares\MiddlewareBase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class LoggerMiddleware extends MiddlewareBase
{
    private const REDACTED_HEADERS = ['authorization'];

    private const REDACTED_VALUE = '***';

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    protected function onRequest(RequestInterface $request): RequestInterface
    {
        $this->logger->debug(sprintf('Request: %s', self::uri($request)));
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
            $options,
        ) {
            $this->logger->debug(
                sprintf('Request OK: %s', self::uri($request)),
                [
                    'request' => [
                        'method' => $request->getMethod(),
                        'body' => (string) $request->getBody(),
                        'headers' => self::headers($request),
                    ],
                    'response' => [
                        'body' => (string) $request->getBody(),
                        'headers' => self::headers($request),
                    ],
                    'options' => $options,
                ],
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
            $options,
        ) {
            $reasonArr = [];
            if ($reason instanceof Throwable) {
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
                sprintf('Request REJECT: %s', self::uri($request)),
                [
                    'request' => [
                        'method' => $request->getMethod(),
                        'body' => (string) $request->getBody(),
                        'headers' => self::headers($request),
                    ],
                    'reason' => $reasonArr,
                    'options' => $options,
                ],
            );
            return new RejectedPromise($reason);
        };
    }

    // The query string carries the whole SAS, so it never reaches the log.
    private static function uri(RequestInterface $request): string
    {
        return (string) $request->getUri()->withQuery('');
    }

    /**
     * @return array<string[]>
     */
    private static function headers(RequestInterface $request): array
    {
        $headers = $request->getHeaders();
        foreach ($headers as $name => $values) {
            if (in_array(strtolower((string) $name), self::REDACTED_HEADERS, true)) {
                $headers[$name] = [self::REDACTED_VALUE];
            }
        }

        return $headers;
    }
}
