<?php declare(strict_types=1);

namespace Hyperized\File\Traits;

trait CreateStaticSelf
{
    public static function create(...$args): self
    {
        return new static(...$args);
    }
}