<?php declare(strict_types=1);

namespace Hyperized\File\Types\Posix;

use Hyperized\File\Collections\Files;
use Hyperized\File\Traits\CreateStaticSelf;
use Hyperized\File\Types\System\User as SystemUser;

class Directory
{
    use CreateStaticSelf;

    protected static array $dotFiles = ['.', '..'];
    protected static int $defaultMode = 0777;
    protected Path $path;
    protected User $owner;
    protected Group $group;
    protected Mode $mode;

    public function __construct(Path $path, ?User $owner = null, ?Group $group = null, ?Mode $mode = null)
    {
        $this->path = $path;
        $this->owner = $owner ?? new User((new SystemUser())->getUsername());
        $this->group = $group ?? new Group((new SystemUser())->getUsername());
        $this->mode = $mode ?? new Mode(self::$defaultMode);
    }

    private static function scanFilesInDirectory(string $path): array
    {
        return scandir($path);
    }

    public function getFiles(): Files
    {
        $filesInDirectory = array_diff(self::scanFilesInDirectory($this->path->getValue()), self::$dotFiles);
        $files = new Files();

        array_walk($filesInDirectory, function ($file) use ($files) {
            $files->add((new File(
                new Path($this->path->getValue() . DIRECTORY_SEPARATOR . $file),
                null,
                null,
                null,
                false
            )));
        });

        return $files;
    }
}