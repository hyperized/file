<?php declare(strict_types=1);

namespace Hyperized\File\Exceptions;

use RuntimeException;

final class LookupFailed extends RuntimeException implements FileThrowable
{
    public static function userByName(string $name): self
    {
        return new self(sprintf('Could not resolve user by name: %s', $name));
    }

    public static function userById(int $id): self
    {
        return new self(sprintf('Could not resolve user by id: %d', $id));
    }

    public static function groupByName(string $name): self
    {
        return new self(sprintf('Could not resolve group by name: %s', $name));
    }

    public static function groupById(int $id): self
    {
        return new self(sprintf('Could not resolve group by id: %d', $id));
    }

    public static function processUser(): self
    {
        return new self('Could not resolve the current process user');
    }

    public static function fileOwner(string $path): self
    {
        return new self(sprintf('Could not stat owner of: %s', $path));
    }

    public static function fileGroup(string $path): self
    {
        return new self(sprintf('Could not stat group of: %s', $path));
    }

    public static function fileMode(string $path): self
    {
        return new self(sprintf('Could not stat mode of: %s', $path));
    }
}
