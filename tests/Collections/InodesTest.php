<?php declare(strict_types=1);

use Hyperized\File\Collections\Inodes;
use Hyperized\File\Types\Posix\Directory;
use Hyperized\File\Types\Posix\File;
use Hyperized\File\Types\Posix\Path;

it('counts the inodes it was constructed with', function (): void {
    $inodes = new Inodes(
        new File(Path::fromString('/tmp/a')),
        new Directory(Path::fromString('/tmp/b')),
    );
    expect(count($inodes))->toBe(2)
        ->and($inodes->toArray())->toHaveCount(2);
});

it('iterates in insertion order', function (): void {
    $inodes = new Inodes(
        new File(Path::fromString('/tmp/a')),
        new File(Path::fromString('/tmp/b')),
    );
    $paths = array_map(fn ($i): string => $i->path->value, iterator_to_array($inodes));
    expect($paths)->toBe(['/tmp/a', '/tmp/b']);
});

it('returns a new collection when adding', function (): void {
    $first = new Inodes(new File(Path::fromString('/tmp/a')));
    $second = $first->add(new File(Path::fromString('/tmp/b')));
    expect(count($first))->toBe(1)
        ->and(count($second))->toBe(2);
});
