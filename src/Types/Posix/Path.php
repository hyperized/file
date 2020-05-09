<?php declare(strict_types=1);

namespace Hyperized\File\Types\Posix;

use Hyperized\ValueObjects\Abstracts\Strings\ByteArray;

class Path extends ByteArray
{
    protected bool $isDirectory;
    protected bool $isRelative;
    protected bool $isRelativeToHome;
    protected string $realPath;

    protected function __construct(string $value)
    {
        parent::__construct($value);
        $this->isDirectory = static::pathIsDirectory($value);
        $this->isRelative = static::pathIsRelative($value);
        $this->isRelativeToHome = static::pathIsRelativeToHome($value);
        // Requires the path to exist :/
        // $this->realPath = realpath($value);
    }

    protected static function pathIsDirectory(string $path): bool
    {
        return self::stringEndsWith($path, '/');
    }

    protected static function pathIsRelative(string $path): bool
    {
        return self::stringStartsWith($path, '.');
    }

    protected static function pathIsRelativeToHome(string $path): bool
    {
        return self::stringStartsWith($path, '~');
    }

    protected static function stringStartsWith(string $string, string $match): bool
    {
        return strpos($string, $match) === 0;
    }

    protected static function stringEndsWith(string $string, string $match): bool
    {
        return substr_compare($string, $match, -strlen($match)) === 0;
    }

    public function isDirectory(): bool
    {
        return $this->isDirectory;
    }

    public function isRelative(): bool
    {
        return $this->isRelative;
    }

    public function isRelativeToHome(): bool
    {
        return $this->isRelativeToHome;
    }

    public function getRealPath(): string
    {
        return $this->realPath;
    }

}
