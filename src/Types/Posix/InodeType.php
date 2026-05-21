<?php declare(strict_types=1);

namespace Hyperized\File\Types\Posix;

enum InodeType: string
{
    case File = 'file';
    case Directory = 'directory';
    case SymbolicLink = 'symlink';
}
