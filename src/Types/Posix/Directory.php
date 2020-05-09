<?php declare(strict_types=1);

namespace Hyperized\File\Types\Posix;

use Hyperized\File\Traits\CreateStaticSelf;

class Directory extends File
{
    use CreateStaticSelf;

    protected static array $dotFiles = ['.', '..'];
    protected static int $defaultMode = 0777;

//    public function getFiles(): Files
//    {
//        $filesInDirectory = array_diff(self::scanFilesInDirectory($this->path->getValue()), self::$dotFiles);
//        $files = new Files();
//
//        array_walk($filesInDirectory, function ($file) use ($files) {
//            $files->add((new File(
//                new Path($this->path->getValue() . DIRECTORY_SEPARATOR . $file),
//                null,
//                null,
//                null,
//                false
//            )));
//        });
//
//        return $files;
//    }
//
//    private static function scanFilesInDirectory(string $path): array
//    {
//        return \Safe\scandir($path);
//    }
}