<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Common;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\AbstractLogger;

class EchoLogger extends AbstractLogger
{
    // phpcs:disable
    public function log($level, $message, array $context = [])
    {
        // phpcs:enable
        echo $message;
        echo PHP_EOL;
        if (array_key_exists('request', $context)) {
            /** @var RequestInterface $request */
            $request = $context['request'];
            echo $request->getBody();
            echo PHP_EOL;
        }

        if (array_key_exists('response', $context)) {
            /** @var ResponseInterface $request */
            $request = $context['response'];
            echo $request->getBody();
            echo PHP_EOL;
        }

        if (array_key_exists('reason', $context)) {
            var_export($context['reason']);
            echo PHP_EOL;
        }

        if (array_key_exists('options', $context)) {
            var_export($context['options']);
            echo PHP_EOL;
        }

        ob_clean();
        ob_flush();
    }
}
