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
        try {
            if ($this->word->getImage()) {
                $this->wordServices->removeWordImageFile($this->word);
            }

            $targetDirectory = $this->wordImageUploadDir . $this->word->getImageRelativePath();

            if (!is_dir($targetDirectory)) {
                if (!@mkdir($targetDirectory, 0775, true) && !is_dir($targetDirectory)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $targetDirectory));
                }
            }

            $imageFile->move(
                $targetDirectory,
                $newFilename
            );

            $relativePath = '/' . $this->wordImageUploadDirRelative . $this->word->getImageRelativePath() . $newFilename;
            $this->word->setImage($relativePath);

            $this->entityManager->persist($this->word);
            $this->entityManager->flush();

            return $this->word->getImage();
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to process word image: ' . $e->getMessage(), 0, $e);
        }
    }
}