<?php declare(strict_types=1);

namespace Hyperized\File\Tests\Types\Posix;

use Hyperized\File\Types\Posix\Path;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertSame;

class PathTest extends TestCase
{
    public function testFromString(): void
    {
        assertSame(
            'hello',
            Path::fromString('hello')
                ->getValue()
        );
    }

    public function testPathIsADirectory(): void
    {
        self::assertTrue(
            Path::fromString('/home/')
                ->isDirectory()
        );
    }

    public function testPathIsNotADirectory(): void
    {
        self::assertFalse(
            Path::fromString('some_file')
                ->isDirectory()
        );
    }

    public function testPathIsRelative(): void
    {
        self::assertTrue(
            Path::fromString('./home/')
                ->isRelative()
        );
    }

    public function testPathIsNotRelative(): void
    {
        self::assertFalse(
            Path::fromString('/home/')
                ->isRelative()
        );
    }

    public function testPathIsRelativeToHome(): void
    {
        self::assertTrue(
            Path::fromString('~/home/')
                ->isRelativeToHome()
        );
    }

    public function testPathIsNotRelativeToHome(): void
    {
        self::assertFalse(
            Path::fromString('/home/')
                ->isRelativeToHome()
        );
    }
}
