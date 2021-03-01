<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Path;

use Keboola\FileStorage\ProviderInterface;

interface AbsolutePathInterface
{
    public function getProvider(): ProviderInterface;

    public function getAbsoluteUrl(): string;

    public function getRelativePath(): RelativePathInterface;
}
