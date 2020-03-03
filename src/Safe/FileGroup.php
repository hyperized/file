<?php declare(strict_types=1);

namespace Hyperized\File\Safe;

use Hyperized\File\Exceptions\CouldNotGetGroupId;

/**
 * @param string $filename
 * @return int
 * @throws CouldNotGetGroupId
 */
function filegroup(string $filename): int
{
    set_error_handler(static function ($severity, $message, $file, $line) {
        throw new CouldNotGetGroupId($message);
    }, E_WARNING);

    $gid = \filegroup($filename);

    restore_error_handler();

    if ($gid === FALSE) {
        throw new CouldNotGetGroupId(
            'Could not retrieve gid with filegroup()'
        );
    }

    return $gid;
}
