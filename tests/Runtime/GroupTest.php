<?php declare(strict_types=1);

use Hyperized\File\Runtime\EffectiveGroup;
use Hyperized\File\Runtime\RealGroup;

it('returns the real gid of the process', function (): void {
    expect((new RealGroup())->id)->toBe(posix_getgid());
});

it('returns the effective gid of the process', function (): void {
    expect((new EffectiveGroup())->id)->toBe(posix_getegid());
});

it('looks up the name of the effective group', function (): void {
    $entry = requireGrGid(posix_getegid());
    expect((new EffectiveGroup())->name())->toBe($entry['name']);
});

it('looks up the name of the real group', function (): void {
    $entry = requireGrGid(posix_getgid());
    expect((new RealGroup())->name())->toBe($entry['name']);
});
