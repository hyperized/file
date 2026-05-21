<?php declare(strict_types=1);

namespace Hyperized\File\Collections;

use ArrayIterator;
use Countable;
use Hyperized\File\Types\Posix\Inode;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, Inode>
 */
final class Inodes implements IteratorAggregate, Countable
{
    /** @var list<Inode> */
    private array $inodes;

    public function __construct(Inode ...$inodes)
    {
        $this->inodes = array_values($inodes);
    }

    public function add(Inode $inode): self
    {
        $copy = new self(...$this->inodes);
        $copy->inodes[] = $inode;
        return $copy;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->inodes);
    }

    public function count(): int
    {
        return count($this->inodes);
    }

    /** @return list<Inode> */
    public function toArray(): array
    {
        return $this->inodes;
    }
}
