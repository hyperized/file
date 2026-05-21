<?php declare(strict_types=1);

namespace Hyperized\File\Reconcile;

final readonly class Change
{
    public function __construct(
        public ChangeKind $kind,
        public ?string $from,
        public ?string $to,
    ) {
    }
}
