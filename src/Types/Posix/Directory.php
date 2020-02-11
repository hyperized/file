<?php declare(strict_types=1);

namespace Hyperized\File\Types\Posix;

class Directory
{
    protected static int $defaultMode = 0777;

    public function __construct(Path $path, ?string $owner = null, ?string $group = null, ?Mode $mode = null)
    {

    }
}