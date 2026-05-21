<?php declare(strict_types=1);

use Hyperized\File\Runtime\Process;

it('exposes the current process identity', function (): void {
    $entry = requirePwUid(posix_geteuid());
    $process = new Process();
    expect($process->username)->toBe($entry['name'])
        ->and($process->userId)->toBe($entry['uid'])
        ->and($process->groupId)->toBe($entry['gid'])
        ->and($process->homeDirectory->value)->toBe($entry['dir'])
        ->and($process->shell->value)->toBe($entry['shell']);
});
