<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Tests\Common;

// phpcs:disable
if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    require_once 'EchoLogger.php7';
} else {
    require_once 'EchoLogger.php8';
}
// phpcs:enable
