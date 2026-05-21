<?php declare(strict_types=1);

namespace Hyperized\File\Runtime;

final readonly class RealGroup extends Group
{
    public function __construct()
    {
        parent::__construct(posix_getgid());
    }
}
