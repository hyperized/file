<?php


use Hyperized\File\Types\Posix\File;
use Hyperized\File\Types\Posix\Group;
use Hyperized\File\Types\Posix\Mode;
use Hyperized\File\Types\Posix\Path;
use Hyperized\File\Types\Posix\User;

include 'vendor/autoload.php';

$file = File
    ::create(
        new Path('/tmp/php-test'),
        new User('myuser'),
        new Group('mygroup'),
        new Mode(0755)
    )
    ->setContents("Hello world!\n");

var_dump($file->getContents());
