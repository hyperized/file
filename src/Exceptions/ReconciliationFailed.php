<?php declare(strict_types=1);

namespace Hyperized\File\Exceptions;

use RuntimeException;
use Throwable;

final class ReconciliationFailed extends RuntimeException implements FileThrowable
{
    public static function touch(string $path, ?Throwable $previous = null): self
    {
        return new self(sprintf('Could not touch: %s', $path), 0, $previous);
    }

    public static function mkdir(string $path, ?Throwable $previous = null): self
    {
        return new self(sprintf('Could not mkdir: %s', $path), 0, $previous);
    }

    public static function symlink(string $path, string $target, ?Throwable $previous = null): self
    {
        return new self(sprintf('Could not symlink %s -> %s', $path, $target), 0, $previous);
    }

    public static function chmod(string $path, int $mode, ?Throwable $previous = null): self
    {
        return new self(sprintf('Could not chmod %s to 0%o', $path, $mode), 0, $previous);
    }

    public static function chown(string $path, int $uid, ?Throwable $previous = null): self
    {
        return new self(sprintf('Could not chown %s to uid %d', $path, $uid), 0, $previous);
    }

    public static function chgrp(string $path, int $gid, ?Throwable $previous = null): self
    {
        return new self(sprintf('Could not chgrp %s to gid %d', $path, $gid), 0, $previous);
    }

    public static function unlink(string $path, ?Throwable $previous = null): self
    {
        return new self(sprintf('Could not unlink: %s', $path), 0, $previous);
    }

    public static function write(string $path, ?Throwable $previous = null): self
    {
        return new self(sprintf('Could not write to: %s', $path), 0, $previous);
    }

    public static function read(string $path, ?Throwable $previous = null): self
    {
        return new self(sprintf('Could not read: %s', $path), 0, $previous);
    }

    public static function typeMismatch(string $path, string $expected, string $actual): self
    {
        return new self(sprintf('Path %s exists but is a %s, expected %s', $path, $actual, $expected));
    }
}
