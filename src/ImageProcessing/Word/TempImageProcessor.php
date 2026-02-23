<?php

declare(strict_types=1);


namespace App\ImageProcessing\Word;


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
    public function process(UploadedFile $imageFile): string
    {
        $extension = $imageFile->guessExtension() ?? $imageFile->getClientOriginalExtension();
        $newFilename = uniqid('temp_', true) . '.' . $extension;

        $this->fileManager->upload($imageFile, $this->wordImageUploadDirTemp, $newFilename);

        $basePath = '/' . ltrim($this->wordImageUploadDirRelative, '/');
        return rtrim($basePath, '/') . '/temp/' . $newFilename;
    }
}