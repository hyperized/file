<?php declare(strict_types=1);

namespace Hyperized\File\Types\Posix;

final readonly class SymbolicLink extends Inode
{
    public function __construct(
        Path $path,
        public Path $target,
        ?User $owner = null,
        ?Group $group = null,
    ) {
        parent::__construct($path, $owner, $group, null);
    }

    public function type(): InodeType
    {
        return InodeType::SymbolicLink;
    }
}
