<?php declare(strict_types=1);

namespace Hyperized\File\Reconcile;

use Countable;
use Hyperized\File\Types\Posix\Path;

final readonly class Report implements Countable
{
    /** @param list<Change> $changes */
    public function __construct(
        public Path $path,
        public array $changes,
    ) {
    }

    public function changed(): bool
    {
        return $this->changes !== [];
    }

    public function hasKind(ChangeKind $kind): bool
    {
        foreach ($this->changes as $change) {
            if ($change->kind === $kind) {
                return true;
            }
        }
        return false;
    }

    public function count(): int
    {
        return count($this->changes);
    }
}
