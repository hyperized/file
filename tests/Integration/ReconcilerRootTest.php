<?php declare(strict_types=1);

use Hyperized\File\Reconcile\ChangeKind;
use Hyperized\File\Reconcile\Reconciler;
use Hyperized\File\Types\Posix\File;
use Hyperized\File\Types\Posix\Group;
use Hyperized\File\Types\Posix\Mode;
use Hyperized\File\Types\Posix\Path;
use Hyperized\File\Types\Posix\SymbolicLink;
use Hyperized\File\Types\Posix\User;

uses()->group('integration');

const ROOT_TEST_TARGET_UID = 1000;
const ROOT_TEST_TARGET_GID = 1000;

$requiresRoot = fn (): bool => posix_geteuid() !== 0;
$requiresRootMessage = 'integration suite requires root for chown/chgrp/lchown/lchgrp';

it('chowns a file to a different uid', function (): void {
    $path = uniqueFixturePath('root-chown');
    touch($path);
    $report = (new Reconciler())->reconcile(new File(
        Path::fromString($path),
        owner: User::fromInteger(ROOT_TEST_TARGET_UID),
    ));
    expect(requireStat($path)['uid'])->toBe(ROOT_TEST_TARGET_UID)
        ->and($report->hasKind(ChangeKind::OwnerChanged))->toBeTrue();
})->skip($requiresRoot, $requiresRootMessage);

it('chgrps a file to a different gid', function (): void {
    $path = uniqueFixturePath('root-chgrp');
    touch($path);
    $report = (new Reconciler())->reconcile(new File(
        Path::fromString($path),
        group: Group::fromInteger(ROOT_TEST_TARGET_GID),
    ));
    expect(requireStat($path)['gid'])->toBe(ROOT_TEST_TARGET_GID)
        ->and($report->hasKind(ChangeKind::GroupChanged))->toBeTrue();
})->skip($requiresRoot, $requiresRootMessage);

it('lchowns a symlink without following', function (): void {
    $target = uniqueFixturePath('root-lchown-target');
    touch($target);
    $link = uniqueFixturePath('root-lchown-link');
    symlink($target, $link);
    $report = (new Reconciler())->reconcile(new SymbolicLink(
        Path::fromString($link),
        Path::fromString($target),
        owner: User::fromInteger(ROOT_TEST_TARGET_UID),
    ));
    expect(requireLstat($link)['uid'])->toBe(ROOT_TEST_TARGET_UID)
        ->and(requireStat($target)['uid'])->not->toBe(ROOT_TEST_TARGET_UID)
        ->and($report->hasKind(ChangeKind::OwnerChanged))->toBeTrue();
})->skip($requiresRoot, $requiresRootMessage);

it('lchgrps a symlink without following', function (): void {
    $target = uniqueFixturePath('root-lchgrp-target');
    touch($target);
    $link = uniqueFixturePath('root-lchgrp-link');
    symlink($target, $link);
    $report = (new Reconciler())->reconcile(new SymbolicLink(
        Path::fromString($link),
        Path::fromString($target),
        group: Group::fromInteger(ROOT_TEST_TARGET_GID),
    ));
    expect(requireLstat($link)['gid'])->toBe(ROOT_TEST_TARGET_GID)
        ->and($report->hasKind(ChangeKind::GroupChanged))->toBeTrue();
})->skip($requiresRoot, $requiresRootMessage);

it('chmods a file owned by another user', function (): void {
    $path = uniqueFixturePath('root-chmod');
    touch($path);
    chown($path, ROOT_TEST_TARGET_UID);
    chmod($path, 0o600);
    (new Reconciler())->reconcile(new File(
        Path::fromString($path),
        mode: Mode::fromInteger(0o644),
    ));
    clearstatcache(true, $path);
    expect(fileperms($path) & 0o7777)->toBe(0o644);
})->skip($requiresRoot, $requiresRootMessage);
