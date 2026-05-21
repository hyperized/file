<?php declare(strict_types=1);

namespace Hyperized\File\Types\Posix;

use Hyperized\File\Exceptions\LookupFailed;
use Stringable;

final readonly class User implements Stringable
{
    public int $id;

    private function __construct(int $id)
    {
        if ($id < 0) {
            throw LookupFailed::userById($id);
        }
        $this->id = $id;
    }

    public static function fromInteger(int $id): self
    {
        return new self($id);
    }

    public static function fromString(string $name): self
    {
        if ($name === '') {
            throw LookupFailed::userByName($name);
        }
        $entry = posix_getpwnam($name);
        if ($entry === false) {
            throw LookupFailed::userByName($name);
        }
        return new self($entry['uid']);
    }

    public function name(): string
    {
        $entry = posix_getpwuid($this->id);
        if ($entry === false) {
            throw LookupFailed::userById($this->id);
        }
        return $entry['name'];
    }

    public function equals(self $other): bool
    {
        return $this->id === $other->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
