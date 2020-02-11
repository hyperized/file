<?php declare(strict_types=1);

namespace Hyperized\File\Types\System;

use Hyperized\File\Types\Posix\Path;

class Process
{
    protected array $user;
    protected Path $homeDirectory;
    protected Path $shell;

    public function __construct()
    {
        $this->user = self::getProcessUserInformation();
        $this->homeDirectory = $this->getHomeDirectory();
        $this->shell = $this->getShellPath();
    }

    public function getUsername(): string
    {
        return $this->user['name'];
    }

    public function getUserId(): int
    {
        return $this->user['uid'];
    }

    public function getGroupId(): int
    {
        return $this->user['gid'];
    }

    public function getHomeDirectory(): Path
    {
        return new Path($this->user['dir']);
    }

    public function getShellPath(): Path
    {
        return new Path($this->user['shell']);
    }

    protected static function getProcessUserInformation(): array
    {
        return posix_getpwuid(posix_geteuid());
    }
}