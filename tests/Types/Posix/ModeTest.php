<?php declare(strict_types=1);

use Hyperized\File\Exceptions\InvalidMode;
use Hyperized\File\Types\Posix\Mode;

describe('Mode::fromInteger', function (): void {
    it('accepts valid POSIX mode bits', function (int $value): void {
        expect(Mode::fromInteger($value)->value)->toBe($value);
    })->with([0, 0o644, 0o755, 0o777, 0o7777]);

    it('rejects negative modes', function (): void {
        Mode::fromInteger(-1);
    })->throws(InvalidMode::class);

    it('rejects out-of-range modes', function (): void {
        Mode::fromInteger(0o10000);
    })->throws(InvalidMode::class);
});

describe('Mode::fromOctalString', function (): void {
    it('parses 4-digit octal strings', function (): void {
        expect(Mode::fromOctalString('0755')->value)->toBe(0o755);
    });

    it('parses 3-digit octal strings', function (): void {
        expect(Mode::fromOctalString('644')->value)->toBe(0o644);
    });

    it('rejects non-octal strings', function (string $value): void {
        Mode::fromOctalString($value);
    })->with(['', '8', '0999', 'abcd', '12345'])->throws(InvalidMode::class);
});

describe('Mode::fromStatMode', function (): void {
    it('masks off file-type bits from a stat mode', function (): void {
        $statMode = 0o100644;
        expect(Mode::fromStatMode($statMode)->value)->toBe(0o644);
    });
});

describe('Mode formatting and equality', function (): void {
    it('renders as a zero-padded octal string', function (int $value, string $expected): void {
        expect(Mode::fromInteger($value)->toOctalString())->toBe($expected);
    })->with([
        [0o644, '0644'],
        [0o755, '0755'],
        [0o7, '0007'],
        [0, '0000'],
    ]);

    it('uses the octal string for __toString', function (): void {
        expect((string) Mode::fromInteger(0o755))->toBe('0755');
    });

    it('compares by value', function (): void {
        expect(Mode::fromInteger(0o644)->equals(Mode::fromInteger(0o644)))->toBeTrue()
            ->and(Mode::fromInteger(0o644)->equals(Mode::fromInteger(0o755)))->toBeFalse();
    });
});
