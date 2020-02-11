<?php declare(strict_types=1);

namespace Hyperized\File\Collections;

use ArrayIterator;
use Countable;
use Hyperized\File\Types\Posix\File;
use IteratorAggregate;

class Files implements IteratorAggregate, Countable
{
    protected array $files;

    public function __construct(File ...$files)
    {
        $this->files = $files;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->files);
    }

    public function add(File $file): void
    {
        $this->files[] = $file;
    }

    public function count()
    {
        return count($this->files);
    }
}