<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Common;

use Psr\Log\AbstractLogger;

class EchoLogger extends AbstractLogger
{
    // phpcs:disable
    public function log($level, $message, array $context = [])
    {
        // phpcs:enable
        echo sprintf('[%s] %s', $level, $message);
        echo PHP_EOL;
        var_export($context);
        echo PHP_EOL;

        ob_clean();
        ob_flush();
    }
}
