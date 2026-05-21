<?php declare(strict_types=1);

use Hyperized\File\Exceptions\FileThrowable;
use Hyperized\File\Exceptions\InvalidMode;
use Hyperized\File\Exceptions\InvalidPath;
use Hyperized\File\Exceptions\LookupFailed;
use Hyperized\File\Exceptions\ReconciliationFailed;

describe('FileThrowable marker', function (): void {
    it('is implemented by every concrete exception in this library', function (string $class): void {
        /** @var class-string<Throwable> $class */
        $reflection = new ReflectionClass($class);
        expect($reflection->implementsInterface(FileThrowable::class))->toBeTrue();
    })->with([
        InvalidPath::class,
        InvalidMode::class,
        LookupFailed::class,
        ReconciliationFailed::class,
    ]);
});

describe('InvalidPath factories', function (): void {
    it('builds a descriptive empty-path error', function (): void {
        expect(InvalidPath::empty()->getMessage())->toContain('empty');
    });

    it('builds a descriptive null-byte error', function (): void {
        expect(InvalidPath::containsNullByte("a\0b")->getMessage())->toContain('null byte');
    });
});

describe('InvalidMode factories', function (): void {
    it('reports the out-of-range value', function (): void {
        expect(InvalidMode::outOfRange(0o10000)->getMessage())->toContain((string) 0o10000);
    });

    it('reports the invalid string', function (): void {
        expect(InvalidMode::invalidOctalString('zzz')->getMessage())->toContain('zzz');
    });
});

describe('LookupFailed factories', function (): void {
    it('names the failure mode for each lookup', function (): void {
        expect(LookupFailed::userByName('alice')->getMessage())->toContain('alice')
            ->and(LookupFailed::userById(42)->getMessage())->toContain('42')
            ->and(LookupFailed::groupByName('staff')->getMessage())->toContain('staff')
            ->and(LookupFailed::groupById(20)->getMessage())->toContain('20')
            ->and(LookupFailed::processUser()->getMessage())->toContain('process')
            ->and(LookupFailed::fileOwner('/x')->getMessage())->toContain('/x')
            ->and(LookupFailed::fileGroup('/x')->getMessage())->toContain('/x')
            ->and(LookupFailed::fileMode('/x')->getMessage())->toContain('/x');
    });
});

describe('ReconciliationFailed factories', function (): void {
    it('produces a message containing the path for each operation', function (): void {
        expect(ReconciliationFailed::touch('/x')->getMessage())->toContain('/x')
            ->and(ReconciliationFailed::mkdir('/x')->getMessage())->toContain('/x')
            ->and(ReconciliationFailed::symlink('/x', '/y')->getMessage())->toContain('/x')
            ->and(ReconciliationFailed::chmod('/x', 0o644)->getMessage())->toContain('/x')
            ->and(ReconciliationFailed::chown('/x', 0)->getMessage())->toContain('/x')
            ->and(ReconciliationFailed::chgrp('/x', 0)->getMessage())->toContain('/x')
            ->and(ReconciliationFailed::unlink('/x')->getMessage())->toContain('/x')
            ->and(ReconciliationFailed::write('/x')->getMessage())->toContain('/x')
            ->and(ReconciliationFailed::read('/x')->getMessage())->toContain('/x')
            ->and(ReconciliationFailed::typeMismatch('/x', 'file', 'directory')->getMessage())->toContain('directory');
    });
});
