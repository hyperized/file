<?php declare(strict_types=1);

namespace Hyperized\File\Types\System;

class Capabilities
{
    protected bool $hasPosixExtension;

    public function __construct()
    {
        $this->hasPosixExtension = self::hasPosixExtension();
    }

    protected static function hasPosixExtension(): bool
    {
        return extension_loaded('posix');
    }

}