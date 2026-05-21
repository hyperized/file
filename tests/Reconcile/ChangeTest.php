<?php declare(strict_types=1);

use Hyperized\File\Reconcile\Change;
use Hyperized\File\Reconcile\ChangeKind;

it('exposes kind, from and to', function (): void {
    $change = new Change(ChangeKind::ModeChanged, '0644', '0755');
    expect($change->kind)->toBe(ChangeKind::ModeChanged)
        ->and($change->from)->toBe('0644')
        ->and($change->to)->toBe('0755');
});

it('exposes the full set of change kinds', function (): void {
    expect(array_map(fn (ChangeKind $k): string => $k->value, ChangeKind::cases()))
        ->toEqualCanonicalizing([
            'created',
            'mode_changed',
            'owner_changed',
            'group_changed',
            'retargeted',
            'removed',
            'content_written',
        ]);
});
