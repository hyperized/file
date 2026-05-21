<?php declare(strict_types=1);

namespace Hyperized\File\Types\Posix;

use Hyperized\File\Exceptions\InvalidMode;
use Stringable;

final readonly class Mode implements Stringable
{
    public const int PERMISSION_MASK = 0o7777;

    public int $value;

    private function __construct(int $value)
    {
        if ($value < 0 || $value > self::PERMISSION_MASK) {
            throw InvalidMode::outOfRange($value);
        }
        $this->value = $value;
    }

    public static function fromInteger(int $value): self
    {
        return new self($value);
    }

    public static function fromOctalString(string $value): self
    {
        if (preg_match('/^0?[0-7]{1,4}$/', $value) !== 1) {
            throw InvalidMode::invalidOctalString($value);
        }
        return new self((int) octdec($value));
    }

    public static function fromStatMode(int $statMode): self
    {
        return new self($statMode & self::PERMISSION_MASK);
    }

    public function toOctalString(): string
    {
        return '0' . str_pad(decoct($this->value), 3, '0', STR_PAD_LEFT);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->toOctalString();
    }
}
