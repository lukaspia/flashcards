<?php

declare(strict_types=1);


namespace App\ImageProcessing\Word;


use App\Entity\Word;
use App\ImageProcessing\Word\WordImageProcessorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class TempImageProcessor implements WordImageProcessorInterface
{
    public function __construct(
        private string $wordImageUploadDirTemp,
        private string $wordImageUploadDirRelative,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(UploadedFile $imageFile, string $newFilename): string
    {
        $imageFile->move(
            $this->wordImageUploadDirTemp,
            $newFilename
        );

        return '/' . $this->wordImageUploadDirRelative . 'temp/' . $newFilename;
    }
}