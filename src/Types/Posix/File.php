<?php declare(strict_types=1);

namespace Hyperized\File\Types\Posix;

use Hyperized\File\Traits\CreateStaticSelf;
use Hyperized\File\Types\System\User as SystemUser;
use InvalidArgumentException;

class File
{
    use CreateStaticSelf;

    protected static int $defaultMode = 0666;
    protected Path $path;
    protected User $owner;
    protected Mode $mode;

    protected bool $exists;
    protected bool $relative;
    protected bool $relativeToHome;

//    protected $fileHandler;

    public function __construct(Path $path, ?User $owner = null, ?Mode $mode = null)
    {
        // If this is true, fail fast.
        if (self::pathIsDirectory($path->getValue())) {
            throw new InvalidArgumentException(
                'Provided File path (' . $path->getValue() . ') is in fact a directory.'
            );
        }

        // Set requested file information
        $this->path = $path;
        $this->owner = $owner ?? new User((new SystemUser())->getUsername());
        $this->mode = $mode ?? new Mode(self::$defaultMode);

        // Generic path information
        $this->exists = self::fileExists($path->getValue());
        $this->relative = self::pathIsRelative($path->getValue());
        $this->relativeToHome = self::pathIsRelativeToHome($path->getValue());

        // To process file, we need to know if it exists or not
        if (!$this->exists) {
            $this->touchIfRequired();
        }

        // Ensure permissions are correct
        $this->chmodIfRequired();
        // Ensure ownership is correct
        $this->chownIfRequired();

        // TODO
        // Obtain real path
        // Obtain file descriptor
        // Ensure we can handle ~ and relative paths
    }

    protected static function pathIsDirectory(string $path): bool
    {
        return self::stringEndsWith($path, '/');
    }

    protected static function stringEndsWith(string $string, string $match): bool
    {
        return substr_compare($string, $match, -strlen($match)) === 0;
    }

    protected static function fileExists(string $path): bool
    {
        return file_exists($path);
    }

    protected static function pathIsRelative(string $path): bool
    {
        return self::stringStartsWith($path, '.');
    }

    protected static function stringStartsWith(string $string, string $match): bool
    {
        return strpos($string, $match) === 0;
    }

    protected static function pathIsRelativeToHome(string $path): bool
    {
        return self::stringStartsWith($path, '~');
    }

    protected function touchIfRequired(): void
    {
        if (!file_exists($this->path->getValue())) {
            if (!touch($this->path->getValue())) {
                throw new InvalidArgumentException('Could not touch File ' . $this->path->getValue());
            }
            clearstatcache();
        }
    }

    protected function chmodIfRequired(): void
    {
        if (decoct($this->mode->getValue()) !== self::getOctalPermissions($this->path->getValue())) {
            if (!chmod($this->path->getValue(), $this->mode->getValue())) {
                throw new InvalidArgumentException(
                    'Could not chmod File ' . $this->path->getValue() . ' to: ' . $this->mode->getValue()
                );
            }
            clearstatcache();
        }
    }

    protected static function getOctalPermissions(string $path): int
    {
        return (int)substr(sprintf('%o', fileperms($path)), -4);
    }

    protected function chownIfRequired(): void
    {
        if ($this->owner->getValue() !== self::getCurrentFileOwner($this->path->getValue())) {
            if (!chown($this->path->getValue(), $this->mode->getValue())) {
                throw new InvalidArgumentException(
                    'Could not chown File ' . $this->path->getValue() . ' to: ' . $this->mode->getValue()
                );
            }
            clearstatcache();
        }
    }

    protected static function getCurrentFileOwner(string $path): string
    {
        return self::getUsernameByUid(fileowner($path));
    }

    protected static function getUsernameByUid(int $uid): string
    {
        return posix_getpwuid($uid)['name'];
    }

//    protected static function getRealPath(string $path): string
//    {
//        return realpath($path);
//    }
//
//    protected static function envVariableIsSet(string $env): bool
//    {
//        return null !== getenv($env);
//    }

    public function remove(): void
    {
        unlink($this->path->getValue());
        clearstatcache();
    }

    public function setContents(string $content): self
    {
        if (false === file_put_contents($this->path->getValue(), $content)) {
            throw new InvalidArgumentException(
                'Could not write contents to file (' . $this->path->getValue() . ')'
            );
        }

        return $this;
    }

    public function getContents(): string
    {
        $contents = file_get_contents($this->path->getValue());
        if (false === $contents) {
            throw new InvalidArgumentException(
                'Could not read contents from file (' . $this->path->getValue() . ')'
            );
        }
        return $contents;
    }
}