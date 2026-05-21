<?php declare(strict_types=1);

use Hyperized\File\Types\Posix\Directory;
use Hyperized\File\Types\Posix\File;
use Hyperized\File\Types\Posix\Group;
use Hyperized\File\Types\Posix\InodeType;
use Hyperized\File\Types\Posix\Mode;
use Hyperized\File\Types\Posix\Path;
use Hyperized\File\Types\Posix\SymbolicLink;
use Hyperized\File\Types\Posix\User;

describe('File', function (): void {
    it('captures the full inode descriptor', function (): void {
        $file = new File(
            Path::fromString('/tmp/x'),
            User::fromInteger(501),
            Group::fromInteger(20),
            Mode::fromInteger(0o644),
        );
        expect($file->type())->toBe(InodeType::File)
            ->and($file->path->value)->toBe('/tmp/x')
            ->and($file->owner?->id)->toBe(501)
            ->and($file->group?->id)->toBe(20)
            ->and($file->mode?->value)->toBe(0o644);
    });

    it('allows owner, group and mode to be unmanaged', function (): void {
        $file = new File(Path::fromString('/tmp/x'));
        expect($file->owner)->toBeNull()
            ->and($file->group)->toBeNull()
            ->and($file->mode)->toBeNull();
    });
});

describe('Directory', function (): void {
    it('reports itself as a directory', function (): void {
        $dir = new Directory(Path::fromString('/tmp/d'));
        expect($dir->type())->toBe(InodeType::Directory);
    });
});

describe('SymbolicLink', function (): void {
    it('carries a separate target path', function (): void {
        $link = new SymbolicLink(
            Path::fromString('/tmp/link'),
            Path::fromString('/tmp/dest'),
            User::fromInteger(501),
            Group::fromInteger(20),
        );
        expect($link->type())->toBe(InodeType::SymbolicLink)
            ->and($link->path->value)->toBe('/tmp/link')
            ->and($link->target->value)->toBe('/tmp/dest')
            ->and($link->mode)->toBeNull();
    });
});
