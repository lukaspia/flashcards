<?php

namespace App\ImageProcessing\Word;

use App\Entity\Word;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface WordImageProcessorInterface
{
    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $imageFile
     * @param \App\Entity\Word|null $word
     * @return string
     */
    public function process(UploadedFile $imageFile, ?Word $word = null): string;
}