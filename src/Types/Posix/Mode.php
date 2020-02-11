<?php declare(strict_types=1);

namespace Hyperized\File\Types\Posix;

use Hyperized\ValueObjects\Abstracts\Integers\ValueObject;

class Mode extends ValueObject
{
    // Todo oct int value object
    protected int $min = 100;
    protected int $max = 9999;
}
