<?php declare(strict_types=1);

namespace Hyperized\File\Types\Posix;

abstract readonly class Inode
{
    public function __construct(
        public Path $path,
        public ?User $owner = null,
        public ?Group $group = null,
        public ?Mode $mode = null,
    ) {
    }

    abstract public function type(): InodeType;
}
