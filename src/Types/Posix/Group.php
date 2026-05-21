<?php declare(strict_types=1);

namespace Hyperized\File\Types\Posix;

use Hyperized\File\Exceptions\LookupFailed;
use Stringable;

final readonly class Group implements Stringable
{
    public int $id;

    private function __construct(int $id)
    {
        if ($id < 0) {
            throw LookupFailed::groupById($id);
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
            throw LookupFailed::groupByName($name);
        }
        $entry = posix_getgrnam($name);
        if ($entry === false) {
            throw LookupFailed::groupByName($name);
        }
        return new self($entry['gid']);
    }

    public function name(): string
    {
        $entry = posix_getgrgid($this->id);
        if ($entry === false) {
            throw LookupFailed::groupById($this->id);
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
