<?php

namespace App\Utils\File;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;

class ImageResizer
{
    /**
     * @var Imagine
     */
    private  $_imagine;

    public function __construct()
    {
        $this->_imagine = new Imagine();
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
        $imagineFile->resize(
            new Box($targetWidth, $targetHeight)
        )->save($targetFilePath);

        return $targetFilename;
    }
}