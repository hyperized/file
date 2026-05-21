<?php declare(strict_types=1);

use Hyperized\File\Exceptions\LookupFailed;
use Hyperized\File\Types\Posix\User;

describe('User::fromInteger', function (): void {
    it('constructs from a uid', function (): void {
        expect(User::fromInteger(0)->id)->toBe(0);
    });

    it('rejects negative ids', function (): void {
        User::fromInteger(-1);
    })->throws(LookupFailed::class);
});

describe('User::fromString', function (): void {
    it('resolves an existing username to a uid', function (): void {
        $entry = requirePwUid(posix_geteuid());
        expect(User::fromString($entry['name'])->id)->toBe($entry['uid']);
    });

    it('rejects an empty name', function (): void {
        User::fromString('');
    })->throws(LookupFailed::class);

    it('rejects an unknown name', function (): void {
        User::fromString('definitely-not-a-real-user-' . bin2hex(random_bytes(6)));
    })->throws(LookupFailed::class);
});

describe('User name lookup', function (): void {
    it('returns the name of the current effective uid', function (): void {
        $entry = requirePwUid(posix_geteuid());
        expect(User::fromInteger($entry['uid'])->name())->toBe($entry['name']);
    });

    it('fails when the uid does not exist', function (): void {
        User::fromInteger(0x6FFFFFFF)->name();
    })->throws(LookupFailed::class);
});

describe('User equality and casts', function (): void {
    it('compares by id', function (): void {
        expect(User::fromInteger(42)->equals(User::fromInteger(42)))->toBeTrue()
            ->and(User::fromInteger(42)->equals(User::fromInteger(43)))->toBeFalse();
    });

    it('casts to string as the id', function (): void {
        expect((string) User::fromInteger(42))->toBe('42');
    });
});
