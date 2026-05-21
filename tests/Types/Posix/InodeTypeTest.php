<?php declare(strict_types=1);

use Hyperized\File\Types\Posix\InodeType;

it('exposes the three POSIX inode kinds used by the library', function (): void {
    expect(InodeType::cases())->toHaveCount(3)
        ->and(InodeType::File->value)->toBe('file')
        ->and(InodeType::Directory->value)->toBe('directory')
        ->and(InodeType::SymbolicLink->value)->toBe('symlink');
});
