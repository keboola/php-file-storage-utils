<?php

declare(strict_types=1);

namespace Keboola\FileStorage\S3;

use Keboola\FileStorage\ProviderInterface;

class S3Provider implements ProviderInterface
{
    public const NAME = 's3';

    public function getName(): string
    {
        return self::NAME;
    }
}
