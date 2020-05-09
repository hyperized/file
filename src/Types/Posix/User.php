<?php declare(strict_types=1);

namespace Hyperized\File\Types\Posix;

use Hyperized\File\Exceptions\CouldNot;
use Hyperized\ValueObjects\Abstracts\Integers\Integer;

class User extends Integer
{
    protected static function getUserByName(string $name): array
    {
        $user = posix_getpwnam($name);
        if (is_array($user) && $user['uid'] !== null) {
            return $user;
        }
        throw CouldNot::getUserByName($name);
    }

    public static function fromString(string $value): self
    {
        return new static(static::getUserByName($value)['uid']);
    }
}
