<?php declare(strict_types=1);

use Hyperized\File\Runtime\EffectiveUser;
use Hyperized\File\Runtime\RealUser;

it('returns the real uid of the process', function (): void {
    expect((new RealUser())->id)->toBe(posix_getuid());
});

it('returns the effective uid of the process', function (): void {
    expect((new EffectiveUser())->id)->toBe(posix_geteuid());
});

it('looks up the name of the effective user', function (): void {
    $entry = requirePwUid(posix_geteuid());
    expect((new EffectiveUser())->name())->toBe($entry['name']);
});

it('looks up the name of the real user', function (): void {
    $entry = requirePwUid(posix_getuid());
    expect((new RealUser())->name())->toBe($entry['name']);
});
