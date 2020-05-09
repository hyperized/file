<?php

use Hyperized\File\Types\Posix\File;
use Hyperized\File\Types\Posix\Group;
use Hyperized\File\Types\Posix\Mode;
use Hyperized\File\Types\Posix\Path;
use Hyperized\File\Types\Posix\User;

include 'vendor/autoload.php';

$file = File
    ::create(
        Path::fromString('/dev/null/test'),
        User::fromString('ggeijteman'),
        Group::fromString('staff'),
        Mode::fromInteger(755)
    );

var_dump($file);
