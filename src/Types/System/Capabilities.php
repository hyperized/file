<?php declare(strict_types=1);

namespace Hyperized\File\Types\System;

use function extension_loaded;

final readonly class Capabilities
{
    public bool $hasPosixExtension;

    public function __construct()
    {
        $this->hasPosixExtension = extension_loaded('posix');
    }

    public static function detect(): self
    {
        return new self();
    }
}
