<?php declare(strict_types=1);

namespace Hyperized\File\Reconcile;

use Hyperized\File\Exceptions\ReconciliationFailed;
use Hyperized\File\Types\Posix\Directory;
use Hyperized\File\Types\Posix\File;
use Hyperized\File\Types\Posix\Inode;
use Hyperized\File\Types\Posix\InodeType;
use Hyperized\File\Types\Posix\Mode;
use Hyperized\File\Types\Posix\Path;
use Hyperized\File\Types\Posix\SymbolicLink;

final class Reconciler
{
    private const int S_IFMT = 0o170000;
    private const int S_IFDIR = 0o040000;
    private const int S_IFLNK = 0o120000;

    public function reconcile(Inode $desired): Report
    {
        $path = $desired->path->value;
        clearstatcache(true, $path);
        $current = $this->lstatOrNull($path);

        $changes = [];

        if ($current === null) {
            $this->create($desired);
            $changes[] = new Change(ChangeKind::Created, null, $path);
            clearstatcache(true, $path);
            $current = $this->lstatOrNull($path);
            if ($current === null) {
                throw ReconciliationFailed::touch($path);
            }
        } else {
            $actualType = $this->detectType($current['mode']);
            if ($actualType !== $desired->type()) {
                throw ReconciliationFailed::typeMismatch($path, $desired->type()->value, $actualType->value);
            }

            if ($desired instanceof SymbolicLink) {
                $currentTarget = $this->readlinkOrNull($path);
                if ($currentTarget !== $desired->target->value) {
                    $this->retarget($desired);
                    $changes[] = new Change(
                        ChangeKind::Retargeted,
                        $currentTarget,
                        $desired->target->value,
                    );
                    clearstatcache(true, $path);
                    $current = $this->lstatOrNull($path);
                    if ($current === null) {
                        throw ReconciliationFailed::symlink($path, $desired->target->value);
                    }
                }
            }
        }

        if ($desired->mode !== null && !($desired instanceof SymbolicLink)) {
            $currentMode = $current['mode'] & Mode::PERMISSION_MASK;
            if ($currentMode !== $desired->mode->value) {
                if (!$this->silent(static fn (): bool => chmod($path, $desired->mode->value))) {
                    throw ReconciliationFailed::chmod($path, $desired->mode->value);
                }
                $changes[] = new Change(
                    ChangeKind::ModeChanged,
                    Mode::fromInteger($currentMode)->toOctalString(),
                    $desired->mode->toOctalString(),
                );
            }
        }

        if ($desired->owner !== null && $current['uid'] !== $desired->owner->id) {
            $owner = $desired->owner;
            $ok = $desired instanceof SymbolicLink
                ? $this->silent(static fn (): bool => lchown($path, $owner->id))
                : $this->silent(static fn (): bool => chown($path, $owner->id));
            if (!$ok) {
                throw ReconciliationFailed::chown($path, $owner->id);
            }
            $changes[] = new Change(
                ChangeKind::OwnerChanged,
                (string) $current['uid'],
                (string) $owner->id,
            );
        }

        if ($desired->group !== null && $current['gid'] !== $desired->group->id) {
            $group = $desired->group;
            $ok = $desired instanceof SymbolicLink
                ? $this->silent(static fn (): bool => lchgrp($path, $group->id))
                : $this->silent(static fn (): bool => chgrp($path, $group->id));
            if (!$ok) {
                throw ReconciliationFailed::chgrp($path, $group->id);
            }
            $changes[] = new Change(
                ChangeKind::GroupChanged,
                (string) $current['gid'],
                (string) $group->id,
            );
        }

        return new Report($desired->path, $changes);
    }

    public function remove(Path $path): Report
    {
        $value = $path->value;
        clearstatcache(true, $value);
        $current = $this->lstatOrNull($value);
        if ($current === null) {
            return new Report($path, []);
        }

        $type = $this->detectType($current['mode']);
        $ok = $type === InodeType::Directory
            ? $this->silent(static fn (): bool => rmdir($value))
            : $this->silent(static fn (): bool => unlink($value));
        if (!$ok) {
            throw ReconciliationFailed::unlink($value);
        }
        return new Report($path, [new Change(ChangeKind::Removed, $value, null)]);
    }

    public function writeContents(File $file, string $contents): Report
    {
        $report = $this->reconcile($file);
        $path = $file->path->value;
        $existing = file_exists($path) ? file_get_contents($path) : false;
        if ($existing === $contents) {
            return $report;
        }
        $written = $this->silent(static fn () => file_put_contents($path, $contents));
        if ($written === false) {
            throw ReconciliationFailed::write($path);
        }
        return new Report(
            $file->path,
            [...$report->changes, new Change(
                ChangeKind::ContentWritten,
                $existing === false ? null : (string) strlen($existing),
                (string) strlen($contents),
            )],
        );
    }

    public function readContents(File $file): string
    {
        $path = $file->path->value;
        if (!is_file($path)) {
            throw ReconciliationFailed::read($path);
        }
        $contents = $this->silent(static fn () => file_get_contents($path));
        if ($contents === false) {
            throw ReconciliationFailed::read($path);
        }
        return $contents;
    }

    private function create(Inode $desired): void
    {
        $path = $desired->path->value;
        if ($desired instanceof Directory) {
            if (!$this->silent(static fn (): bool => mkdir($path, 0o777, false))) {
                throw ReconciliationFailed::mkdir($path);
            }
            return;
        }
        if ($desired instanceof SymbolicLink) {
            $target = $desired->target->value;
            if (!$this->silent(static fn (): bool => symlink($target, $path))) {
                throw ReconciliationFailed::symlink($path, $target);
            }
            return;
        }
        if (!$this->silent(static fn (): bool => touch($path))) {
            throw ReconciliationFailed::touch($path);
        }
    }

    private function retarget(SymbolicLink $desired): void
    {
        $path = $desired->path->value;
        if (!$this->silent(static fn (): bool => unlink($path))) {
            throw ReconciliationFailed::unlink($path);
        }
        $target = $desired->target->value;
        if (!$this->silent(static fn (): bool => symlink($target, $path))) {
            throw ReconciliationFailed::symlink($path, $target);
        }
    }

    private function detectType(int $statMode): InodeType
    {
        return match ($statMode & self::S_IFMT) {
            self::S_IFDIR => InodeType::Directory,
            self::S_IFLNK => InodeType::SymbolicLink,
            default => InodeType::File,
        };
    }

    /** @return array<int|string, int>|null */
    private function lstatOrNull(string $path): ?array
    {
        if (!file_exists($path) && !is_link($path)) {
            return null;
        }
        $stat = $this->silent(static fn () => lstat($path));
        return $stat === false ? null : $stat;
    }

    private function readlinkOrNull(string $path): ?string
    {
        if (!is_link($path)) {
            return null;
        }
        $target = $this->silent(static fn () => readlink($path));
        return $target === false ? null : $target;
    }

    /**
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    private function silent(callable $operation): mixed
    {
        set_error_handler(static fn (): bool => true);
        try {
            return $operation();
        } finally {
            restore_error_handler();
        }
    }
}
