<?php

namespace Hyperized\File\Runtime;

use Hyperized\File\Exceptions\CouldNot;

abstract class User
{
    protected int $user_id;

    protected static function getUserById(int $user_id): array
    {
        $user = posix_getpwuid($user_id);
        if (empty($user)) {
            throw CouldNot::getUserById($user_id);
        }
        return $user;
    }

    public function getId(): int
    {
        return $this->user_id;
    }

    public function getName(): string
    {
        return $this->getAsArray()['name'];
    }
}