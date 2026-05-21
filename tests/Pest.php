<?php declare(strict_types=1);

use Hyperized\File\Exceptions\LookupFailed;

final class TestFixtureRoot
{
    private static string $base = '';

    public static function path(string $suffix = ''): string
    {
        if (self::$base === '') {
            self::$base = sys_get_temp_dir() . '/hyperized-file-tests-' . bin2hex(random_bytes(6));
            mkdir(self::$base, 0o777, true);
            register_shutdown_function([self::class, 'cleanup']);
        }
        return $suffix === '' ? self::$base : self::$base . '/' . ltrim($suffix, '/');
    }

    public static function cleanup(): void
    {
        if (self::$base !== '') {
            removeTestTree(self::$base);
        }
    }
}

function fileFixturePath(string $suffix = ''): string
{
    return TestFixtureRoot::path($suffix);
}

function uniqueFixturePath(string $name): string
{
    return fileFixturePath($name . '-' . bin2hex(random_bytes(4)));
}

function removeTestTree(string $path): void
{
    if (!file_exists($path) && !is_link($path)) {
        return;
    }
    if (is_link($path) || !is_dir($path)) {
        @unlink($path);
        return;
    }
    foreach (scandir($path) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        removeTestTree($path . '/' . $entry);
    }
    @rmdir($path);
}

/**
 * @return array{name: string, passwd: string, uid: int, gid: int, gecos: string, dir: string, shell: string}
 */
function requirePwUid(int $uid): array
{
    $entry = posix_getpwuid($uid);
    if ($entry === false) {
        throw LookupFailed::userById($uid);
    }
    return $entry;
}

/**
 * @return array{name: string, passwd: string, gid: int, members: list<string>}
 */
function requireGrGid(int $gid): array
{
    $entry = posix_getgrgid($gid);
    if ($entry === false) {
        throw LookupFailed::groupById($gid);
    }
    return $entry;
}

/**
 * @return array{0: int, 1: int, 2: int, 3: int, 4: int, 5: int, 6: int, 7: int, 8: int, 9: int, 10: int, 11: int, 12: int, dev: int, ino: int, mode: int, nlink: int, uid: int, gid: int, rdev: int, size: int, atime: int, mtime: int, ctime: int, blksize: int, blocks: int}
 */
function requireStat(string $path): array
{
    $stat = stat($path);
    if ($stat === false) {
        throw new RuntimeException('stat failed for ' . $path);
    }
    return $stat;
}

/**
 * @return array{0: int, 1: int, 2: int, 3: int, 4: int, 5: int, 6: int, 7: int, 8: int, 9: int, 10: int, 11: int, 12: int, dev: int, ino: int, mode: int, nlink: int, uid: int, gid: int, rdev: int, size: int, atime: int, mtime: int, ctime: int, blksize: int, blocks: int}
 */
function requireLstat(string $path): array
{
    $stat = lstat($path);
    if ($stat === false) {
        throw new RuntimeException('lstat failed for ' . $path);
    }
    return $stat;
}
