<?php declare(strict_types=1);

namespace Hyperized\File\Types\System;

use function extension_loaded;

class Capabilities
{
    protected bool $hasPosixExtension;

    public static function new(): self
    {
        return new static();
    }

    protected function __construct()
    {
        $this->hasPosixExtension = self::hasPosixExtension();
    }

    protected static function hasPosixExtension(): bool
    {
        return extension_loaded('posix');
    }

    public function getHasPosixExtension(): bool
    {
        return $this->hasPosixExtension;
    }
}
