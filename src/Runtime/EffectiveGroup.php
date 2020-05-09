<?php

namespace Hyperized\File\Runtime;

use Hyperized\File\Exceptions\CouldNot;

class EffectiveGroup extends Group
{
    public function __construct()
    {
        $this->group_id = static::getEffectiveGroupId();
    }

    protected static function getEffectiveGroupId(): int
    {
        $group_id = posix_getegid();
        if ($group_id === null) {
            throw CouldNot::getGroupId();
        }
        return $group_id;
    }

    public function getAsArray(): array
    {
        return static::getGroupById(static::getEffectiveGroupId());
    }
}