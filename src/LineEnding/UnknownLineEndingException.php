<?php

declare(strict_types=1);

namespace Keboola\FileStorage\LineEnding;

use Exception;

class UnknownLineEndingException extends Exception
{
    public function __construct()
    {
        parent::__construct('Unknown line ending.');
    }
}
