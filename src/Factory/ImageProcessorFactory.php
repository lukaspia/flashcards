<?php

declare(strict_types=1);


namespace App\Factory;


use App\Entity\Word;
use App\Factory\WordImageProcessorFactoryInterface;
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
        private string $wordImageUploadDir,
        private string $wordImageUploadDirRelative,
        private string $wordImageUploadDirTemp
    ) {
    }

    /**
     * @inheritDoc
     */
    public function createProcessor(int $wordId): WordImageProcessorInterface
    {
        $word = $this->entityManager->getRepository(Word::class)->find($wordId);

        if ($word) {
            return new WordImageProcessor(
                $this->entityManager,
                $this->wordServices,
                $this->wordImageUploadDir,
                $this->wordImageUploadDirRelative,
                $word
            );
        }

        return new TempImageProcessor(
            $this->wordImageUploadDirTemp,
            $this->wordImageUploadDirRelative
        );
    }
}