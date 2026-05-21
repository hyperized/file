<?php declare(strict_types=1);

use Hyperized\File\Exceptions\LookupFailed;
use Hyperized\File\Types\Posix\Group;

describe('Group::fromInteger', function (): void {
    it('constructs from a gid', function (): void {
        expect(Group::fromInteger(0)->id)->toBe(0);
    });

    it('rejects negative ids', function (): void {
        Group::fromInteger(-1);
    })->throws(LookupFailed::class);
});

describe('Group::fromString', function (): void {
    it('resolves an existing group name to a gid', function (): void {
        $entry = requireGrGid(posix_getegid());
        expect(Group::fromString($entry['name'])->id)->toBe($entry['gid']);
    });

    it('rejects an empty name', function (): void {
        Group::fromString('');
    })->throws(LookupFailed::class);

    it('rejects an unknown name', function (): void {
        Group::fromString('definitely-not-a-real-group-' . bin2hex(random_bytes(6)));
    })->throws(LookupFailed::class);
});

describe('Group name lookup', function (): void {
    it('returns the name of the current effective gid', function (): void {
        $entry = requireGrGid(posix_getegid());
        expect(Group::fromInteger($entry['gid'])->name())->toBe($entry['name']);
    });

    it('fails when the gid does not exist', function (): void {
        Group::fromInteger(0x6FFFFFFF)->name();
    })->throws(LookupFailed::class);
});

describe('Group equality and casts', function (): void {
    it('compares by id', function (): void {
        expect(Group::fromInteger(7)->equals(Group::fromInteger(7)))->toBeTrue()
            ->and(Group::fromInteger(7)->equals(Group::fromInteger(8)))->toBeFalse();
    });

    it('casts to string as the id', function (): void {
        expect((string) Group::fromInteger(7))->toBe('7');
    });
});
