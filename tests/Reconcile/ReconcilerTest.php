<?php declare(strict_types=1);

use Hyperized\File\Exceptions\ReconciliationFailed;
use Hyperized\File\Reconcile\ChangeKind;
use Hyperized\File\Reconcile\Reconciler;
use Hyperized\File\Types\Posix\Directory;
use Hyperized\File\Types\Posix\File;
use Hyperized\File\Types\Posix\Group;
use Hyperized\File\Types\Posix\Mode;
use Hyperized\File\Types\Posix\Path;
use Hyperized\File\Types\Posix\SymbolicLink;
use Hyperized\File\Types\Posix\User;

describe('reconcile(File)', function (): void {
    it('creates a missing file', function (): void {
        $path = uniqueFixturePath('create');
        $report = (new Reconciler())->reconcile(new File(Path::fromString($path)));
        expect(file_exists($path))->toBeTrue()
            ->and($report->changed())->toBeTrue()
            ->and($report->hasKind(ChangeKind::Created))->toBeTrue();
    });

    it('reports no change for an already-correct file', function (): void {
        $path = uniqueFixturePath('idempotent');
        $desired = new File(Path::fromString($path));
        (new Reconciler())->reconcile($desired);
        $second = (new Reconciler())->reconcile($desired);
        expect($second->changed())->toBeFalse()
            ->and(count($second))->toBe(0);
    });

    it('applies a chmod when the mode differs', function (): void {
        $path = uniqueFixturePath('chmod');
        touch($path);
        chmod($path, 0o600);
        $report = (new Reconciler())->reconcile(new File(
            Path::fromString($path),
            mode: Mode::fromInteger(0o644),
        ));
        clearstatcache(true, $path);
        expect(fileperms($path) & 0o7777)->toBe(0o644)
            ->and($report->hasKind(ChangeKind::ModeChanged))->toBeTrue();
    });

    it('does not chmod when the mode already matches', function (): void {
        $path = uniqueFixturePath('mode-match');
        touch($path);
        chmod($path, 0o644);
        $report = (new Reconciler())->reconcile(new File(
            Path::fromString($path),
            mode: Mode::fromInteger(0o644),
        ));
        expect($report->hasKind(ChangeKind::ModeChanged))->toBeFalse();
    });

    it('reports no owner change when the uid already matches', function (): void {
        $path = uniqueFixturePath('owner-noop');
        touch($path);
        $report = (new Reconciler())->reconcile(new File(
            Path::fromString($path),
            owner: User::fromInteger(posix_geteuid()),
        ));
        expect($report->hasKind(ChangeKind::OwnerChanged))->toBeFalse();
    });

    it('reports no group change when the gid already matches', function (): void {
        $path = uniqueFixturePath('group-noop');
        touch($path);
        $current = requireStat($path);
        $report = (new Reconciler())->reconcile(new File(
            Path::fromString($path),
            group: Group::fromInteger($current['gid']),
        ));
        expect($report->hasKind(ChangeKind::GroupChanged))->toBeFalse();
    });

    it('refuses to reconcile a path that exists as a different inode type', function (): void {
        $path = uniqueFixturePath('mismatch');
        mkdir($path, 0o755, true);
        (new Reconciler())->reconcile(new File(Path::fromString($path)));
    })->throws(ReconciliationFailed::class, 'expected file');
});

describe('reconcile(Directory)', function (): void {
    it('creates a missing directory', function (): void {
        $path = uniqueFixturePath('mkdir');
        (new Reconciler())->reconcile(new Directory(Path::fromString($path)));
        expect(is_dir($path))->toBeTrue();
    });

    it('chmods a directory to the desired mode', function (): void {
        $path = uniqueFixturePath('dir-chmod');
        mkdir($path, 0o700, true);
        (new Reconciler())->reconcile(new Directory(
            Path::fromString($path),
            mode: Mode::fromInteger(0o755),
        ));
        clearstatcache(true, $path);
        expect(fileperms($path) & 0o7777)->toBe(0o755);
    });
});

describe('reconcile(SymbolicLink)', function (): void {
    it('creates a symlink pointing at the target', function (): void {
        $target = uniqueFixturePath('symlink-target');
        touch($target);
        $link = uniqueFixturePath('symlink');
        $report = (new Reconciler())->reconcile(new SymbolicLink(
            Path::fromString($link),
            Path::fromString($target),
        ));
        expect(is_link($link))->toBeTrue()
            ->and(readlink($link))->toBe($target)
            ->and($report->hasKind(ChangeKind::Created))->toBeTrue();
    });

    it('retargets a symlink whose target has drifted', function (): void {
        $oldTarget = uniqueFixturePath('old-target');
        $newTarget = uniqueFixturePath('new-target');
        touch($oldTarget);
        touch($newTarget);
        $link = uniqueFixturePath('relink');
        symlink($oldTarget, $link);
        $report = (new Reconciler())->reconcile(new SymbolicLink(
            Path::fromString($link),
            Path::fromString($newTarget),
        ));
        expect(readlink($link))->toBe($newTarget)
            ->and($report->hasKind(ChangeKind::Retargeted))->toBeTrue();
    });

    it('does nothing if the symlink already points at the right target', function (): void {
        $target = uniqueFixturePath('stable-target');
        touch($target);
        $link = uniqueFixturePath('stable-link');
        symlink($target, $link);
        $report = (new Reconciler())->reconcile(new SymbolicLink(
            Path::fromString($link),
            Path::fromString($target),
        ));
        expect($report->changed())->toBeFalse();
    });
});

describe('remove', function (): void {
    it('removes an existing file', function (): void {
        $path = uniqueFixturePath('remove');
        touch($path);
        $report = (new Reconciler())->remove(Path::fromString($path));
        expect(file_exists($path))->toBeFalse()
            ->and($report->hasKind(ChangeKind::Removed))->toBeTrue();
    });

    it('removes an existing directory', function (): void {
        $path = uniqueFixturePath('rmdir');
        mkdir($path);
        $report = (new Reconciler())->remove(Path::fromString($path));
        expect(is_dir($path))->toBeFalse()
            ->and($report->hasKind(ChangeKind::Removed))->toBeTrue();
    });

    it('is a no-op when the path is already absent', function (): void {
        $report = (new Reconciler())->remove(Path::fromString(uniqueFixturePath('gone')));
        expect($report->changed())->toBeFalse();
    });
});

describe('create failures', function (): void {
    it('throws when touch cannot create the file', function (): void {
        $path = uniqueFixturePath('no-parent') . '/missing-dir/leaf';
        (new Reconciler())->reconcile(new File(Path::fromString($path)));
    })->throws(ReconciliationFailed::class, 'touch');

    it('throws when mkdir cannot create the directory', function (): void {
        $path = uniqueFixturePath('no-parent') . '/missing-dir/leaf';
        (new Reconciler())->reconcile(new Directory(Path::fromString($path)));
    })->throws(ReconciliationFailed::class, 'mkdir');

    it('throws when symlink cannot be created because the path already exists', function (): void {
        $path = uniqueFixturePath('exists');
        touch($path);
        (new Reconciler())->reconcile(new SymbolicLink(
            Path::fromString($path . '/leaf'),
            Path::fromString($path),
        ));
    })->throws(ReconciliationFailed::class);
});

describe('mutation failures (run as non-root)', function (): void {
    it('throws when chown to a different uid is denied', function (): void {
        $path = uniqueFixturePath('chown-deny');
        touch($path);
        (new Reconciler())->reconcile(new File(
            Path::fromString($path),
            owner: User::fromInteger(0),
        ));
    })->throws(ReconciliationFailed::class, 'chown')
        ->skip(fn (): bool => posix_geteuid() === 0, 'chown success path requires root; this only asserts the non-root failure');

    it('throws when chgrp to a foreign gid is denied', function (): void {
        $path = uniqueFixturePath('chgrp-deny');
        touch($path);
        (new Reconciler())->reconcile(new File(
            Path::fromString($path),
            group: Group::fromInteger(0),
        ));
    })->throws(ReconciliationFailed::class, 'chgrp')
        ->skip(function (): bool {
            if (posix_geteuid() === 0) {
                return true;
            }
            $groups = posix_getgroups();
            $groups = $groups === false ? [] : $groups;
            return in_array(0, $groups, true) || posix_getegid() === 0;
        }, 'chgrp success path requires root, and gid 0 must be foreign to the test user');

    it('throws when rmdir cannot remove a non-empty directory', function (): void {
        $dir = uniqueFixturePath('non-empty');
        mkdir($dir);
        touch($dir . '/child');
        (new Reconciler())->remove(Path::fromString($dir));
    })->throws(ReconciliationFailed::class, 'unlink');
});

describe('writeContents / readContents', function (): void {
    it('writes contents and reconciles in one call', function (): void {
        $path = uniqueFixturePath('write');
        $file = new File(Path::fromString($path), mode: Mode::fromInteger(0o600));
        $report = (new Reconciler())->writeContents($file, 'hello');
        clearstatcache(true, $path);
        expect(file_get_contents($path))->toBe('hello')
            ->and(fileperms($path) & 0o7777)->toBe(0o600)
            ->and($report->hasKind(ChangeKind::ContentWritten))->toBeTrue();
    });

    it('skips writing when the contents already match', function (): void {
        $path = uniqueFixturePath('write-noop');
        file_put_contents($path, 'same');
        $file = new File(Path::fromString($path));
        $report = (new Reconciler())->writeContents($file, 'same');
        expect($report->hasKind(ChangeKind::ContentWritten))->toBeFalse();
    });

    it('reads contents back from the filesystem', function (): void {
        $path = uniqueFixturePath('read');
        file_put_contents($path, 'payload');
        expect((new Reconciler())->readContents(new File(Path::fromString($path))))->toBe('payload');
    });

    it('throws when reading a missing file', function (): void {
        (new Reconciler())->readContents(new File(Path::fromString(uniqueFixturePath('missing'))));
    })->throws(ReconciliationFailed::class);

    it('throws when reading a path that is not a regular file', function (): void {
        $dir = uniqueFixturePath('not-a-file');
        mkdir($dir);
        (new Reconciler())->readContents(new File(Path::fromString($dir)));
    })->throws(ReconciliationFailed::class);

    it('throws when the file is unreadable', function (): void {
        $path = uniqueFixturePath('unreadable');
        touch($path);
        chmod($path, 0o000);
        try {
            (new Reconciler())->readContents(new File(Path::fromString($path)));
        } finally {
            chmod($path, 0o644);
        }
    })->throws(ReconciliationFailed::class)
        ->skip(fn (): bool => posix_geteuid() === 0, 'root can read mode-000 files');

    it('throws when writing to a read-only file', function (): void {
        $path = uniqueFixturePath('readonly');
        file_put_contents($path, 'old');
        chmod($path, 0o400);
        try {
            (new Reconciler())->writeContents(new File(Path::fromString($path)), 'new');
        } finally {
            chmod($path, 0o644);
        }
    })->throws(ReconciliationFailed::class, 'write')
        ->skip(fn (): bool => posix_geteuid() === 0, 'root can write to mode-400 files');
});

describe('symlink retarget failures', function (): void {
    it('throws when the symlink cannot be unlinked during retarget', function (): void {
        $oldTarget = uniqueFixturePath('retarget-old');
        $newTarget = uniqueFixturePath('retarget-new');
        touch($oldTarget);
        touch($newTarget);

        $parent = uniqueFixturePath('retarget-parent');
        mkdir($parent);
        $link = $parent . '/link';
        symlink($oldTarget, $link);
        chmod($parent, 0o500);
        try {
            (new Reconciler())->reconcile(new SymbolicLink(
                Path::fromString($link),
                Path::fromString($newTarget),
            ));
        } finally {
            chmod($parent, 0o700);
        }
    })->throws(ReconciliationFailed::class)
        ->skip(fn (): bool => posix_geteuid() === 0, 'root can unlink inside a 0500 directory');
});
