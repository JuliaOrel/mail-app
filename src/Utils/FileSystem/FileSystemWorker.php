<?php

namespace App\Utils\FileSystem;

use Symfony\Component\Filesystem\Filesystem;

class FileSystemWorker
{

    /**
     * @var Filesystem
     */
    private $_fileSystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->_fileSystem = $filesystem;
    }

    public function createFolderIfNotExist(string $folder)
    {
        if (!$this->_fileSystem->exists($folder)) {
            $this->_fileSystem->mkdir($folder);
        }
    }

    public function remove(string $item)
    {
        if (!$this->_fileSystem->exists($item)) {
            $this->_fileSystem->remove($item);
        }
    }
}