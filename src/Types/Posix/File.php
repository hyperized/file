<?php declare(strict_types=1);

namespace Hyperized\File\Types\Posix;

use Hyperized\File\Exceptions\CouldNotGetFileContents;
use Hyperized\File\Exceptions\CouldNotGetGroup;
use Hyperized\File\Exceptions\CouldNotGetGroupById;
use Hyperized\File\Exceptions\CouldNotGetGroupId;
use Hyperized\File\Exceptions\CouldNotGetUsername;
use Hyperized\File\Exceptions\CouldNotGetUsernameByUserId;
use Hyperized\File\Exceptions\CouldNotRemoveFile;
use Hyperized\File\Exceptions\CouldNotWriteToFile;
use Hyperized\File\Runtime\EffectiveGroup;
use Hyperized\File\Runtime\EffectiveUser;
use Hyperized\File\Traits\CreateStaticSelf;
use InvalidArgumentException;
use Safe\Exceptions\FilesystemException;

class File
{
    use CreateStaticSelf;

    protected static int $defaultMode = 0666;
    protected Path $path;
    protected User $owner;
    protected Group $group;
    protected Mode $mode;

    protected bool $exists;
    protected bool $relative;
    protected bool $relativeToHome;

    protected $inode;

    public function __construct(Path $path, ?User $owner = null, ?Group $group = null, ?Mode $mode = null)
    {
        // Set requested file information
        $this->path = $path;

        $this->owner = $owner ?? User::fromInteger((new EffectiveUser())->getId());
        $this->group = $group ?? Group::fromInteger((new EffectiveGroup())->getId());
        $this->mode = $mode ?? Mode::fromInteger(static::$defaultMode);

        // Generic path information
        $this->exists = self::fileExists($path->getValue());

        //        // If this is true, fail fast.
//        if (self::pathIsDirectory($path->getValue())) {
//            throw new InvalidArgumentException(
//                'Provided File path (' . $path->getValue() . ') is in fact a directory.'
//            );
//        } -> Move to validation?

//        $this->chgrpIfRequired();
//
//        if ($persist) {
//            // To process file, we need to know if it exists or not
//            if (!$this->exists) {
//                $this->touchIfRequired();
//            }
//
//            // Ensure permissions are correct
//            $this->chmodIfRequired();
//            // Ensure ownership is correct
//            $this->chownIfRequired();
//            // Ensure group is correct
//            $this->chgrpIfRequired();
//
//            // TODO
//            // Obtain inode
//            // Obtain real path
//            // Obtain file descriptor
//            // Ensure we can handle ~ and relative paths
//        }

        //$this->inode = self::getInodeByPath($path->getValue());
    }

    public function persist(): bool
    {
        return true; // when successful :)
    }

    protected static function fileExists(string $path): bool
    {
        return file_exists($path);
    }

    protected function touchIfRequired(): void
    {
        if (!file_exists($this->path->getValue())) {
            if (!\Safe\touch($this->path->getValue())) {
                throw new InvalidArgumentException('Could not touch File ' . $this->path->getValue());
            }
            clearstatcache();
        }
    }

    protected function chmodIfRequired(): void
    {
        if (decoct($this->mode->getValue()) !== self::getOctalPermissions($this->path->getValue())) {
            if (!\Safe\chmod($this->path->getValue(), $this->mode->getValue())) {
                throw new InvalidArgumentException(
                    'Could not chmod File ' . $this->path->getValue() . ' to: ' . $this->mode->getValue()
                );
            }
            clearstatcache();
        }
    }

    protected static function getOctalPermissions(string $path): int
    {
        return (int)\Safe\substr(\Safe\sprintf('%o', fileperms($path)), -4);
    }

    protected function chownIfRequired(): void
    {
        if ($this->owner->getValue() !== self::getCurrentFileOwner($this->path->getValue())) {
            if (!\Safe\chown($this->path->getValue(), $this->owner->getValue())) {
                throw new InvalidArgumentException(
                    'Could not chown File ' . $this->path->getValue() . ' to: ' . $this->owner->getValue()
                );
            }
            clearstatcache();
        }
    }

    protected function chgrpIfRequired(): void
    {
        if ($this->owner->getValue() !== self::getCurrentFileGroup($this->path->getValue())) {
            if (!\Safe\chgrp($this->path->getValue(), $this->group->getValue())) {
                throw new InvalidArgumentException(
                    'Could not chgrp File ' . $this->path->getValue() . ' to: ' . $this->group->getValue()
                );
            }
            clearstatcache();
        }
    }

    /**
     * @param string $path
     * @return string
     * @throws CouldNotGetUsername
     */
    protected static function getCurrentFileOwner(string $path): string
    {
        try {
            $username = self::getUsernameByUid(\Safe\fileowner($path));
        } catch (FilesystemException | CouldNotGetUsernameByUserId $exception) {
            throw new CouldNotGetUsername(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        return $username;
    }

    /**
     * @param string $path
     * @return string
     * @throws CouldNotGetGroup
     */
    protected static function getCurrentFileGroup(string $path): string
    {
        try {
            $gid = \Hyperized\File\Safe\filegroup($path);
        } catch (CouldNotGetGroupId $exception) {
            throw new CouldNotGetGroup(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        if ($gid === FALSE) {
            throw new CouldNotGetGroup(
                'Could not get group of path: ' . $path . ' because gid is empty',
            );
        }

        try {
            $group = self::getGroupByGid($gid);
        } catch (CouldNotGetGroupById $exception) {
            throw new CouldNotGetGroup(
                'Could not get group of path: ' . $path . ' because group id could not be obtained',
                $exception->getCode(),
                $exception
            );
        }

        return $group;
    }

    /**
     * @param int $uid
     * @return string
     * @throws CouldNotGetUsernameByUserId
     */
    protected static function getUsernameByUid(int $uid): string
    {
        $user = posix_getpwuid($uid);

        if (!is_array($user)) {
            throw new CouldNotGetUsernameByUserId(
                'getpwuid yields no result for uid: ' . $uid
            );
        }

        if (!array_key_exists('name', $user)) {
            throw new CouldNotGetUsernameByUserId(
                'getpwuid results do not contain a valid name field for uid: ' . $uid
            );
        }

        return $user['name'];
    }

    /**
     * @param int $gid
     * @return string
     * @throws CouldNotGetGroupById
     */
    protected static function getGroupByGid(int $gid): string
    {
        $gid = posix_getgrgid($gid)['name'];
        if ($gid !== '') {
            throw new CouldNotGetGroupById(
                'Could not get group id of gid: ' . (string)$gid,
            );
        }
        return $gid;
    }

//    protected static function getInodeByPath(string $path): int
//    {
//        try {
//            return \Safe\fileinode($path);
//        } catch (FilesystemException $exception) {
//        }
//    }

//
//    protected static function envVariableIsSet(string $env): bool
//    {
//        return null !== getenv($env);
//    }

    /**
     * @throws CouldNotRemoveFile
     */
    public function remove(): void
    {
        try {
            \Safe\unlink($this->path->getValue());
        } catch (FilesystemException $exception) {
            throw new CouldNotRemoveFile(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
        clearstatcache();
    }

    /**
     * @param string $content
     * @return File
     * @throws CouldNotWriteToFile
     */
    public function setContents(string $content): self
    {
        try {
            \Safe\file_put_contents($this->path->getValue(), $content);
        } catch (FilesystemException $exception) {
            throw new CouldNotWriteToFile(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        return $this;
    }

    /**
     * @throws CouldNotGetFileContents
     */
    public function getContents(): string
    {
        try {
            $contents = \Safe\file_get_contents($this->path->getValue());
        } catch (FilesystemException $exception) {
            throw new CouldNotGetFileContents(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
        return $contents;
    }

    public function getInode(): int
    {
        return $this->inode;
    }

    public function getPath(): string
    {
        return $this->path->getValue();
    }
}