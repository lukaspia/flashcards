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
        $newFilename = uniqid('temp_', true) . '.' . $imageFile->guessExtension();

        try {
            if (!is_dir($this->wordImageUploadDirTemp)) {
                if (!@mkdir($this->wordImageUploadDirTemp, 0775, true) && !is_dir($this->wordImageUploadDirTemp)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $this->wordImageUploadDirTemp));
                }
            }

            $imageFile->move(
                $this->wordImageUploadDirTemp,
                $newFilename
            );

            return '/' . $this->wordImageUploadDirRelative . 'temp/' . $newFilename;
        } catch (FileException $e) {
            throw new FileException(sprintf('Could not move the file "%s"', $imageFile->getClientOriginalName()), 0, $e);
        }
    }
}