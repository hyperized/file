<?php

namespace Hyperized\File\Exceptions;

use RuntimeException;

class CouldNot extends RuntimeException
{
    public static function changeGroupPermissions(Path $path): self
    {
        return new self('Could not change group permissions for: ' . $path);
    }

    public static function createFile(File $file): self
    {
        return new self('Could not create file: ' . $file->getPath());
    }

    public static function getFileContents(File $file): self
    {
        return new self('Could not get file contents for: ' . $file->getPath());
    }

    public static function getGroupId(): self
    {
        return new self('Could not get group id');
    }

    public static function getGroupById(int $id): self
    {
        return new self('Could not get group by id: ' . $id);
    }

    public static function getGroupByName(string $name): self
    {
        return new self('Could not get group by name: ' . $name);
    }

    public static function getUserId(): self
    {
        return new self('Could not get user id');
    }

    public static function getUserById(int $id): self
    {
        return new self('Could not get user by id: ' . $id);
    }

    public static function getUserByName(string $name): self
    {
        return new self('Could not get user by name: ' . $name);
    }
}