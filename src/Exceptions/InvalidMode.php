<?php declare(strict_types=1);

namespace Hyperized\File\Exceptions;

use InvalidArgumentException;

final class InvalidMode extends InvalidArgumentException implements FileThrowable
{
    public static function outOfRange(int $value): self
    {
        return new self(sprintf('Mode %d is out of POSIX range [0, 07777]', $value));
    }

    public static function invalidOctalString(string $value): self
    {
        return new self(sprintf('Mode %s is not a valid 1-4 digit octal string', $value));
    }
}
