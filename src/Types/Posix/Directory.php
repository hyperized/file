<?php declare(strict_types=1);

namespace Hyperized\File\Types\Posix;

final readonly class Directory extends Inode
{
    public function type(): InodeType
    {
        return InodeType::Directory;
    }
}
