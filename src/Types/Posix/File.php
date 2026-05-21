<?php declare(strict_types=1);

namespace Hyperized\File\Types\Posix;

final readonly class File extends Inode
{
    public function type(): InodeType
    {
        return InodeType::File;
    }
}
