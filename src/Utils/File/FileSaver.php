<?php

namespace App\Utils\File;

use App\Utils\FileSystem\FileSystemWorker;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileSaver
{

    /**
     * @var SluggerInterface
     */
    private $_slugger;

    /**
     * @var FileSystemWorker
     */
    private $_fileSystemWorker;

    /**
     * @var string
     */
    private $_uploadsTempDir;

    public function __construct(SluggerInterface $slugger, FileSystemWorker $fileSystemWorker, string $uploadsTempDir)
    {
        $this->_slugger = $slugger;
        $this->_fileSystemWorker = $fileSystemWorker;
        $this->_uploadsTempDir = $uploadsTempDir;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @return string|null
     */
    public function saveUploadedFileIntoTemp(UploadedFile $uploadedFile): ?string
    {
        $origFileName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->_slugger->slug($origFileName);
        $filename = sprintf("%s-%s.%s", $safeFilename, uniqid(), $uploadedFile->guessExtension());

        $this->_fileSystemWorker->createFolderIfNotExist($this->_uploadsTempDir);
        try {
            $uploadedFile->move($this->_uploadsTempDir, $filename);
        } catch (FileException $ex) {
            return null;
        }

        return $filename;
    }
}