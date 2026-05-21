<?php declare(strict_types=1);

namespace Hyperized\File\Runtime;

final readonly class EffectiveUser extends User
{
    public function __construct()
    {
        parent::__construct(posix_geteuid());
    }
}
