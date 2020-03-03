<?php


use Hyperized\File\Exceptions\FileThrowable;
use Hyperized\File\Types\Posix\File;
use Hyperized\File\Types\Posix\Group;
use Hyperized\File\Types\Posix\Mode;
use Hyperized\File\Types\Posix\Path;
use Hyperized\File\Types\Posix\User;

include 'vendor/autoload.php';

$file = File
    ::create(
        new Path('/dev/null/test'),
        new User('ggeijteman'),
        new Group('staff'),
        new Mode(0755),
        false
    );

try {
    $file = File
        ::create(
            new Path('/dev/null/test'),
            new User('ggeijteman'),
            new Group('staff'),
            new Mode(0755),
            false
        );
} catch (FileThrowable $throwable) {
    print 'Exception: ' . $throwable->getMessage();
}

//
//
//
//var_dump($file);

//$directory = \Hyperized\File\Types\Posix\Directory::create(
//    new Path('/tmp'),
//);
//
//try {
//    $file = (new File(new Path('/tmp/jemoeder')))->setContents('jemoeder');
//} catch (FileThrowable $exception) {
//    // user business logic
//}
//
//$files = $directory->getFiles();
//
//var_dump($files);