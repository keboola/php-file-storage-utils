<?php

declare(strict_types=1);

namespace Keboola\FileStorage\Path;

use Keboola\FileStorage\ProviderInterface;

/**
 * Interface RelativePathInterface
 *
 * Abstracts file storage path and differences between S3|AWS approach to store data
 *
 * @package Keboola\Package\FileStorage\Path\FilePath
 */
interface RelativePathInterface
{
    public function getFileName(): string;

    public function getProvider(): ProviderInterface;

    /**
     * return relative path without slash on begging
     * format <root>/<path(optional)>/<file>
     */
    public function getPathname(): string;

    public function getPathnameWithoutRoot(): string;

    public function getPathWithoutRoot(): string;

    public function getPath(): string;

    /**
     * root referee to container (ABS) or bucket (S3)
     */
    public function getRoot(): string;
}
