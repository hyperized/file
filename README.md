# hyperized/file

[![CI](https://github.com/hyperized/file/actions/workflows/ci.yml/badge.svg)](https://github.com/hyperized/file/actions/workflows/ci.yml)
[![Integration](https://github.com/hyperized/file/actions/workflows/integration.yml/badge.svg)](https://github.com/hyperized/file/actions/workflows/integration.yml)

Declarative POSIX file, directory, and symlink state for PHP.

`hyperized/file` lets you describe what an inode on disk should look like
(path, owner, group, mode, target) as an immutable value object, then hand
that description to a `Reconciler` that brings the filesystem in line and
reports back which changes it made. The model is small, immutable, and
ownership-aware - the missing piece between Symfony Filesystem and a full
configuration-management tool.

## Status

The library has been rewritten on PHP 8.3 with `readonly` value objects,
`Stringable`, named enums, and Pest 4. It depends only on `ext-posix` and
the PHP standard library.

## Install

```sh
composer require hyperized/file
```

Requires PHP 8.3+ and the `posix` extension.

## Concepts

```
Inode  ── abstract value object
 ├── File
 ├── Directory
 └── SymbolicLink (carries a target Path)

Path, Mode, User, Group  ── immutable value objects
Reconciler              ── applies an Inode to disk and returns a Report
```

A `File`, `Directory`, or `SymbolicLink` describes a desired state. It
never touches the filesystem during construction.

`Reconciler::reconcile($inode)` inspects the path, creates it if missing,
applies any mode/owner/group differences, and returns a `Report` listing
the changes it made. The call is idempotent: a second reconcile against
the same desired state returns an empty `Report`.

## Quick examples

### Make sure a file exists with a given mode

```php
use Hyperized\File\Reconcile\Reconciler;
use Hyperized\File\Types\Posix\File;
use Hyperized\File\Types\Posix\Mode;
use Hyperized\File\Types\Posix\Path;

$reconciler = new Reconciler();
$report = $reconciler->reconcile(new File(
    Path::fromString('/var/run/app.pid'),
    mode: Mode::fromInteger(0o644),
));

if ($report->changed()) {
    foreach ($report->changes as $change) {
        printf("%s: %s -> %s\n", $change->kind->value, $change->from ?? '-', $change->to ?? '-');
    }
}
```

### Manage a directory tree

```php
use Hyperized\File\Reconcile\Reconciler;
use Hyperized\File\Types\Posix\Directory;
use Hyperized\File\Types\Posix\Mode;
use Hyperized\File\Types\Posix\Path;

$reconciler = new Reconciler();
$reconciler->reconcile(new Directory(
    Path::fromString('/var/cache/myapp'),
    mode: Mode::fromInteger(0o755),
));
```

### Ensure a symlink points at the right target

```php
use Hyperized\File\Reconcile\Reconciler;
use Hyperized\File\Types\Posix\Path;
use Hyperized\File\Types\Posix\SymbolicLink;

(new Reconciler())->reconcile(new SymbolicLink(
    path:   Path::fromString('/usr/local/bin/myapp'),
    target: Path::fromString('/opt/myapp/current/bin/myapp'),
));
```

If the link already exists pointing somewhere else, it is unlinked and
recreated. If it points where you asked, the call is a no-op.

### Write file contents declaratively

```php
$file = new File(
    Path::fromString('/etc/myapp/config.ini'),
    mode: Mode::fromInteger(0o600),
);
$report = $reconciler->writeContents($file, $configIni);
```

`writeContents()` reconciles the file metadata first, then writes the
body only if it differs from what is on disk.

### Read process identity

```php
use Hyperized\File\Runtime\EffectiveUser;
use Hyperized\File\Runtime\RealUser;
use Hyperized\File\Runtime\Process;

(new EffectiveUser())->id;     // posix_geteuid()
(new RealUser())->name();      // resolves to a username
(new Process())->homeDirectory; // a Path value object
```

## Reconciler reports

`Report` is `Countable` and exposes:

```php
$report->changed();             // any work done?
count($report);                 // how many changes
$report->hasKind($changeKind);  // did it do a specific kind?
$report->changes;               // list<Change>
```

`ChangeKind` values:

- `Created`         - the inode did not exist and was created
- `Retargeted`      - a symlink's target was changed
- `ModeChanged`     - chmod was applied
- `OwnerChanged`    - chown / lchown was applied
- `GroupChanged`    - chgrp / lchgrp was applied
- `ContentWritten`  - writeContents wrote new bytes
- `Removed`         - the path was removed by `Reconciler::remove()`

## Exceptions

All library exceptions implement `Hyperized\File\Exceptions\FileThrowable`:

- `InvalidPath` - a `Path` is empty or contains a null byte
- `InvalidMode` - a `Mode` is out of range or not parseable as octal
- `LookupFailed` - a uid/gid/username/groupname could not be resolved
- `ReconciliationFailed` - the filesystem operation failed

Catch `FileThrowable` to handle anything thrown by this library.

## Testing

```sh
make                  # phpstan max + pest
make coverage         # pest with coverage report
make integration      # root-required suite, locally (skips when not root)
make docker-integration   # root-required suite, inside the bundled container
```

The default Pest suite excludes `tests/Integration`, which contains the
tests that require root (real chown/chgrp/lchown/lchgrp changes).
`make docker-integration` builds the `php:8.3-cli`-based image declared
in `Dockerfile` / `docker-compose.yml`, runs `composer install` inside
an isolated `vendor` volume, and executes the integration suite as root.

## CI

GitHub Actions runs three workflows:

- `.github/workflows/ci.yml` — PHPStan max + Pest default suite on the
  PHP 8.3 / 8.4 / 8.5 matrix, plus a `composer normalize --dry-run` gate
  and a coverage artifact on the 8.3 leg.
- `.github/workflows/integration.yml` — `make docker-integration`
  equivalent on Ubuntu, with GHA build cache for the test image.
- `.github/dependabot.yml` — weekly updates for composer dev deps,
  GitHub Actions, and the Dockerfile base image.

## What is intentionally not in scope

- Following symlinks (everything operates on the path as given, via
  `lstat`).
- Recursive directory creation. Pass a parent that already exists, or
  reconcile parents explicitly. This keeps the model predictable.
- Cross-platform Windows support. POSIX only.

## License

MIT.
