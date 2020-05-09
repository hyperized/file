<?php

namespace Hyperized\File\Runtime;

use Hyperized\File\Exceptions\CouldNot;

class RealGroup extends Group
{
    public function __construct()
    {
        $this->group_id = static::getRealGroupId();
    }

    protected static function getRealGroupId(): int
    {
        $group_id = posix_getgid();
        if ($group_id === null) {
            throw CouldNot::getGroupId();
        }
        return $group_id;
    }

    public function getAsArray(): array
    {
        return static::getGroupById(static::getRealGroupId());
    }
}