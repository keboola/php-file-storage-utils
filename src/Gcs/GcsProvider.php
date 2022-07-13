<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Gcs;

use Keboola\FileStorage\ProviderInterface;

class GcsProvider implements ProviderInterface
{
    public const NAME = 'gcs';

    public function getName(): string
    {
        return self::NAME;
    }
}
