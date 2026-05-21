<?php declare(strict_types=1);

use Hyperized\File\Exceptions\InvalidPath;
use Hyperized\File\Types\Posix\Path;

describe('Path construction', function (): void {
    it('keeps the original string', function (): void {
        expect(Path::fromString('/etc/passwd')->value)->toBe('/etc/passwd');
    });

    it('exposes the value via Stringable', function (): void {
        expect((string) Path::fromString('/etc/passwd'))->toBe('/etc/passwd');
    });

    it('rejects empty paths', function (): void {
        Path::fromString('');
    })->throws(InvalidPath::class, 'Path may not be empty');

    it('rejects paths containing a null byte', function (): void {
        Path::fromString("/etc/pa\0sswd");
    })->throws(InvalidPath::class);
});

describe('Path classification', function (): void {
    it('recognises absolute paths', function (string $value, bool $expected): void {
        expect(Path::fromString($value)->isAbsolute())->toBe($expected);
    })->with([
        ['/etc/passwd', true],
        ['/', true],
        ['relative', false],
        ['./relative', false],
        ['~/home', false],
        ['.bashrc', false],
    ]);

    it('recognises relative paths only when neither absolute nor home', function (string $value, bool $expected): void {
        expect(Path::fromString($value)->isRelative())->toBe($expected);
    })->with([
        ['relative', true],
        ['./relative', true],
        ['.bashrc', true],
        ['/etc/passwd', false],
        ['~/home', false],
    ]);

    it('recognises home-relative paths', function (string $value, bool $expected): void {
        expect(Path::fromString($value)->isRelativeToHome())->toBe($expected);
    })->with([
        ['~/home', true],
        ['~', true],
        ['/etc/passwd', false],
        ['relative', false],
    ]);

    it('recognises trailing-separator paths but not the root', function (string $value, bool $expected): void {
        expect(Path::fromString($value)->endsWithSeparator())->toBe($expected);
    })->with([
        ['/etc/', true],
        ['relative/', true],
        ['/etc/passwd', false],
        ['/', false],
    ]);
});

describe('Path equality', function (): void {
    it('treats equal strings as equal', function (): void {
        expect(Path::fromString('/a')->equals(Path::fromString('/a')))->toBeTrue();
    });

    it('treats different strings as different', function (): void {
        expect(Path::fromString('/a')->equals(Path::fromString('/b')))->toBeFalse();
    });
});
