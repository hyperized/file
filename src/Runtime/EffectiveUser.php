<?php

namespace Hyperized\File\Runtime;

use Hyperized\File\Exceptions\CouldNot;

class EffectiveUser extends User
{
    public function __construct()
    {
        $this->user_id = static::getEffectiveuserId();
    }

    protected static function getEffectiveUserId(): int
    {
        $user_id = posix_geteuid();
        if ($user_id === null) {
            throw CouldNot::getUserId();
        }
        return $user_id;
    }

    public function getAsArray(): array
    {
        return static::getuserById(static::getEffectiveuserId());
    }
}