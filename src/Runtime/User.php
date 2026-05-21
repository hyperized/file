<?php declare(strict_types=1);

namespace Hyperized\File\Runtime;

use Hyperized\File\Exceptions\LookupFailed;

abstract readonly class User
{
    public function __construct(public int $id)
    {
    }

    public function name(): string
    {
        $entry = posix_getpwuid($this->id);
        if ($entry === false) {
            throw LookupFailed::userById($this->id);
        }
        return $entry['name'];
    }
}
