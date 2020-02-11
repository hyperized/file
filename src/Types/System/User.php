<?php declare(strict_types=1);

namespace Hyperized\File\Types\System;

class User
{
    protected string $currentUsername;

    public function __construct()
    {
        $this->currentUsername = self::getCurrentUsername();
    }

    protected static function getCurrentUsername(): string
    {
        return get_current_user();
    }

    public function getUsername(): string
    {
        return $this->currentUsername;
    }
}