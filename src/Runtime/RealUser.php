<?php

namespace Hyperized\File\Runtime;

use Hyperized\File\Exceptions\CouldNot;

class RealUser extends User
{
    public function __construct()
    {
        $this->user_id = static::getRealUserId();
    }

    protected static function getRealUserId(): int
    {
        $user_id = posix_getuid();
        if ($user_id === null) {
            throw CouldNot::getUserId();
        }
        return $user_id;
    }

    public function getAsArray(): array
    {
        return static::getuserById(static::getRealUserId());
    }
}