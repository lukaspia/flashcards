<?php

declare(strict_types=1);


namespace App\ImageProcessing\Word;


use App\Entity\Word;
use App\ImageProcessing\Word\WordImageProcessorInterface;
use App\Service\Lesson\WordImageServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class WordImageProcessor implements WordImageProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WordImageServiceInterface $wordServices,
        private string $wordImageUploadDir,
        private string $wordImageUploadDirRelative,
        private Word $word
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(UploadedFile $imageFile, string $newFilename): string
    {
        if ($this->word->getImage()) {
            $this->wordServices->removeWordImageFile($this->word);
        }

        $imageFile->move(
            $this->wordImageUploadDir . $this->word->getImageRelativePath(),
            $newFilename
        );

        $this->word->setImage(
            '/' . $this->wordImageUploadDirRelative . $this->word->getImageRelativePath() . $newFilename
        );

        $this->entityManager->persist($this->word);
        $this->entityManager->flush();

        return $this->word->getImage();
    }
}