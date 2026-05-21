<?php declare(strict_types=1);

use Hyperized\File\Types\System\Capabilities;

it('detects the posix extension on this host', function (): void {
    expect(Capabilities::detect()->hasPosixExtension)->toBe(extension_loaded('posix'));
});

it('returns the same boolean when constructed directly', function (): void {
    expect((new Capabilities())->hasPosixExtension)->toBe(extension_loaded('posix'));
});
