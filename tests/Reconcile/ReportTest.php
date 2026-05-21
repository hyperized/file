<?php declare(strict_types=1);

use Hyperized\File\Reconcile\Change;
use Hyperized\File\Reconcile\ChangeKind;
use Hyperized\File\Reconcile\Report;
use Hyperized\File\Types\Posix\Path;

it('reports no changes when the list is empty', function (): void {
    $report = new Report(Path::fromString('/x'), []);
    expect($report->changed())->toBeFalse()
        ->and(count($report))->toBe(0)
        ->and($report->hasKind(ChangeKind::Created))->toBeFalse();
});

it('reports changes when they have been recorded', function (): void {
    $report = new Report(Path::fromString('/x'), [
        new Change(ChangeKind::Created, null, '/x'),
        new Change(ChangeKind::ModeChanged, '0644', '0755'),
    ]);
    expect($report->changed())->toBeTrue()
        ->and(count($report))->toBe(2)
        ->and($report->hasKind(ChangeKind::Created))->toBeTrue()
        ->and($report->hasKind(ChangeKind::ModeChanged))->toBeTrue()
        ->and($report->hasKind(ChangeKind::OwnerChanged))->toBeFalse();
});
