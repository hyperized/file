<?php declare(strict_types=1);

namespace Hyperized\File\Types\Posix;

use Hyperized\File\Exceptions\CouldNot;
use Hyperized\ValueObjects\Abstracts\Integers\Integer;
use Safe\Exceptions\PosixException;

class Group extends Integer
{
    protected static function getGroupByName(string $name): array
    {
        try {
            return \Safe\posix_getgrnam($name);
        } catch (PosixException $e) {
            throw CouldNot::getGroupByName($name);
        }
    }

    public static function fromString(string $value): self
    {
        return new static(static::getGroupByName($value)['gid']);
    }
}
