<?php

namespace App\Utils\Manager;

use App\Entity\Mailing;
use App\Entity\MailingImage;
use App\Utils\File\ImageResizer;
use App\Utils\FileSystem\FileSystemWorker;
use Doctrine\ORM\EntityManagerInterface;

class MailingImageManager
{

    /**
     * @var EntityManagerInterface
     */
    private $_entityManager;
    /**
     * @var FileSystemWorker
     */
    private $_fileSystemWorker;
    /**
     * @var string
     */
    private $_uploadsTempDir;

    /**
     * @var ImageResizer
     */
    private $_imageResizer;
    public function __construct(EntityManagerInterface $entityManager, FileSystemWorker $fileSystemWorker, ImageResizer $imageResizer, string $uploadsTempDir)
    {
        $this->setEntityManager($entityManager);
        $this->setFileSystemWorker($fileSystemWorker);
        $this->setUploadsTempDir($uploadsTempDir);
        $this->setImageResizer($imageResizer);
    }

    public function saveImageForMailing(string $mailingDir, string $tempImageFileName = null)
    {
        if (!$tempImageFileName) {
            return null;
        }

        $this->_fileSystemWorker->createFolderIfNotExist($mailingDir);
        $filenameId = uniqid();
        $imageSmallParams = [
            "width" => 60,
            "height" => null,
            "newFolder" => $mailingDir,
            "newFilename" => sprintf("%s_%s.jpg", $filenameId, "small"),
        ];
        $imageSmall = $this->_imageResizer->resizesImageAndSave($this->_uploadsTempDir,$tempImageFileName, $imageSmallParams);
        $imageMiddleParams = [
            "width" => 430,
            "height" => null,
            "newFolder" => $mailingDir,
            "newFilename" => sprintf("%s_%s.jpg", $filenameId, "middle"),
        ];
        $imageMiddle = $this->_imageResizer->resizesImageAndSave($this->_uploadsTempDir,$tempImageFileName, $imageMiddleParams);
        $imageBigParams = [
            "width" => null,
            "height" => null,
            "newFolder" => $mailingDir,
            "newFilename" => sprintf("%s_%s.%s", pathinfo($tempImageFileName, PATHINFO_FILENAME), "orig", pathinfo($tempImageFileName, PATHINFO_EXTENSION)),
        ];
        $imageBig = $this->_imageResizer->resizesImageAndSave($this->_uploadsTempDir,$tempImageFileName, $imageBigParams);

        $mailingImage = new MailingImage();
        $mailingImage->setFilenameSmall($imageSmall);
        $mailingImage->setFilenameMiddle($imageMiddle);
        $mailingImage->setFilenameBig($imageBig);
 
        return $mailingImage;
    }

    public function removeImageFromMailing(MailingImage $mailingImage, $mailingDir)
    {
        $smallFilePath = $mailingDir . "/" . $mailingImage->getFilenameSmall();
        $this->_fileSystemWorker->remove($smallFilePath);
        $middleFilePath = $mailingDir . "/" . $mailingImage->getFilenameMiddle();
        $this->_fileSystemWorker->remove($middleFilePath);
        $bigFilePath = $mailingDir . "/" . $mailingImage->getFilenameBig();
        $this->_fileSystemWorker->remove($bigFilePath);

        $mailing = $mailingImage->getMailing();
        $mailing->removeMailingImage($mailingImage);
        $this->_entityManager->flush();
    }

    /**
     * Get the value of entityManager
     */ 
    public function getEntityManager()
    {
        return $this->_entityManager;
    }

    /**
     * Set the value of entityManager
     *
     * @return  self
     */ 
    public function setEntityManager($entityManager)
    {
        $this->_entityManager = $entityManager;

        return $this;
    }

    /**
     * Get the value of fileSystemWorker
     */ 
    public function getFileSystemWorker()
    {
        return $this->_fileSystemWorker;
    }

    /**
     * Set the value of fileSystemWorker
     *
     * @return  self
     */ 
    public function setFileSystemWorker($fileSystemWorker)
    {
        $this->_fileSystemWorker = $fileSystemWorker;

        return $this;
    }

    /**
     * Get the value of uploadsTempDir
     */ 
    public function getUploadsTempDir()
    {
        return $this->_uploadsTempDir;
    }

    /**
     * Set the value of uploadsTempDir
     *
     * @return  self
     */ 
    public function setUploadsTempDir($uploadsTempDir)
    {
        $this->_uploadsTempDir = $uploadsTempDir;

        return $this;
    }

    /**
     * Get the value of imageResizer
     */ 
    public function getImageResizer()
    {
        return $this->_imageResizer;
    }

    /**
     * Set the value of imageResizer
     *
     * @return  self
     */ 
    public function setImageResizer($imageResizer)
    {
        $this->_imageResizer = $imageResizer;

        return $this;
    }
}