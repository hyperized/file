<?php declare(strict_types=1);

namespace Hyperized\File\Runtime;

use Hyperized\File\Exceptions\LookupFailed;

abstract readonly class Group
{
    public function __construct(public int $id)
    {
    }

    public function name(): string
    {
        $entry = posix_getgrgid($this->id);
        if ($entry === false) {
            throw LookupFailed::groupById($this->id);
        }
        return $entry['name'];
    }
}
