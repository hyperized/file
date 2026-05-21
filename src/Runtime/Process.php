<?php declare(strict_types=1);

namespace Hyperized\File\Runtime;

use Hyperized\File\Exceptions\LookupFailed;
use Hyperized\File\Types\Posix\Path;

final readonly class Process
{
    public string $username;
    public int $userId;
    public int $groupId;
    public Path $homeDirectory;
    public Path $shell;

    public function __construct()
    {
        $entry = posix_getpwuid(posix_geteuid());
        if ($entry === false) {
            throw LookupFailed::processUser();
        }
        $this->username = $entry['name'];
        $this->userId = $entry['uid'];
        $this->groupId = $entry['gid'];
        $this->homeDirectory = Path::fromString($entry['dir']);
        $this->shell = Path::fromString($entry['shell']);
    }
}
