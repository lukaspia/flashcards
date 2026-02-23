<?php

declare(strict_types=1);


namespace App\Factory;


use App\Entity\Word;
use App\Factory\WordImageProcessorFactoryInterface;
use App\File\FileManagerInterface;
use App\File\FileNameGeneratorInterface;
use App\ImageProcessing\Word\TempImageProcessor;
use App\ImageProcessing\Word\WordImageProcessor;
use App\ImageProcessing\Word\WordImageProcessorInterface;
use App\Service\Lesson\WordImageServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

readonly class ImageProcessorFactory implements WordImageProcessorFactoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WordImageServiceInterface $wordServices,
        private FileNameGeneratorInterface $fileNameGenerator,
        private FileManagerInterface $fileManager,
        private string $wordImageUploadDir,
        private string $wordImageUploadDirRelative,
        private string $wordImageUploadDirTemp
    ) {
    }

    /**
     * @inheritDoc
     */
    public function createProcessor(?Word $word): WordImageProcessorInterface
    {
        if (!$word) {
            return new TempImageProcessor(
                $this->wordImageUploadDirTemp,
                $this->wordImageUploadDirRelative
            );
        }

        return new WordImageProcessor(
            $this->entityManager,
            $this->wordServices,
            $this->fileNameGenerator,
            $this->fileManager,
            $this->wordImageUploadDir,
            $this->wordImageUploadDirRelative,
            $word
        );
    }
}