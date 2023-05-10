<?php

namespace App\Utils\File;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Component\Filesystem\Filesystem;

class ImageResizer
{
    /**
     * @var Imagine
     */
    private  $_imagine;
    /**
     * @var Filesystem
     */
    private  $_fs;

    public function __construct()
    {
        $this->_imagine = new Imagine();
        $this->_fs = new Filesystem();
    }

    /**
     * @param string
     * @param string
     * @param array
     * @return string
     */
    public function resizesImageAndSave(string $originalFileFolder, string $originalFilename, array $targetParams): string
    {
        $originalFilePath = $originalFileFolder . "/" . $originalFilename;
        list($imageWidth, $imageHeight) = getimagesize($originalFilePath);
        $isOrig = false;
        if (empty($targetParams["width"]) && empty($targetParams["height"])) {
            $targetParams["width"] = $imageWidth;
            $targetParams["height"] = $imageHeight;
            $isOrig = true;
        }
        $ratio = $imageWidth / $imageHeight;
        $targetWidth = $targetParams["width"];
        $targetHeight = $targetParams["height"];

        if ($targetHeight) {
            if ($targetWidth / $targetHeight > $ratio){
                $targetWidth = $targetHeight * $ratio;
            } else {
                $targetHeight = $targetWidth / $ratio;
            }
        } else {
            $targetHeight = $targetWidth / $ratio;
        }

        $targetFolder = $targetParams["newFolder"];
        $targetFilename = $targetParams["newFilename"];

        $targetFilePath = sprintf("%s/%s", $targetFolder, $targetFilename);

        $imagineFile = $this->_imagine->open($originalFilePath);
        if ($isOrig) {
            $this->_fs->copy($originalFilePath, $targetFilePath);
            return $targetFilename;
        }
        $imagineFile->resize(
            new Box($targetWidth, $targetHeight)
        )->save($targetFilePath);

        return $targetFilename;
    }
}