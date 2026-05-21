<?php declare(strict_types=1);

namespace Hyperized\File\Exceptions;

use InvalidArgumentException;

final class InvalidPath extends InvalidArgumentException implements FileThrowable
{
    public static function empty(): self
    {
        return new self('Path may not be empty');
    }

    public static function containsNullByte(string $value): self
    {
        return new self(sprintf('Path may not contain a null byte: %s', addcslashes($value, "\0..\37")));
    }
}
