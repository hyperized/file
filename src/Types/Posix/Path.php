<?php declare(strict_types=1);

namespace Hyperized\File\Types\Posix;

use Hyperized\File\Exceptions\InvalidPath;
use Stringable;

final readonly class Path implements Stringable
{
    public string $value;

    private function __construct(string $value)
    {
        if ($value === '') {
            throw InvalidPath::empty();
        }
        if (str_contains($value, "\0")) {
            throw InvalidPath::containsNullByte($value);
        }
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function isAbsolute(): bool
    {
        return str_starts_with($this->value, '/');
    }

    public function isRelativeToHome(): bool
    {
        return str_starts_with($this->value, '~');
    }

    public function isRelative(): bool
    {
        return !$this->isAbsolute() && !$this->isRelativeToHome();
    }

    public function endsWithSeparator(): bool
    {
        return str_ends_with($this->value, '/') && $this->value !== '/';
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
