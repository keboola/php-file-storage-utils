<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Abs;

use Keboola\FileStorage\ProviderInterface;

class AbsProvider implements ProviderInterface
{
    public const NAME = 'abs';

    public function getName(): string
    {
        return self::NAME;
    }
}
