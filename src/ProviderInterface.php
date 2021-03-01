<?php

declare(strict_types=1);

namespace Keboola\FileStorage;

interface ProviderInterface
{
    public function getName(): string;
}
