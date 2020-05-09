<?php

namespace Hyperized\File\Runtime;

use Hyperized\File\Exceptions\CouldNot;

abstract class Group
{
    protected int $group_id;

    protected static function getGroupById(int $group_id): array
    {
        $group = posix_getgrgid($group_id);
        if (empty($group)) {
            throw CouldNot::getGroupById($group_id);
        }
        return $group;
    }

    public function getId(): int
    {
        return $this->group_id;
    }

    public function getName(): string
    {
        return $this->getAsArray()['name'];
    }
}