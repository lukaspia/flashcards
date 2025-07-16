<?php

namespace App\ImageProcessing\Word;

use App\Entity\Word;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface WordImageProcessorInterface
{
    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $imageFile
     * @param string $newFilename
     * @return string
     */
    public function process(UploadedFile $imageFile, string $newFilename): string;
}