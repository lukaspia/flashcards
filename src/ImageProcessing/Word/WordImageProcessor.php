<?php

declare(strict_types=1);


namespace App\ImageProcessing\Word;


use App\Entity\Word;
use App\File\FileManagerInterface;
use App\File\FileNameGeneratorInterface;
use App\ImageProcessing\Word\WordImageProcessorInterface;
use App\Service\Lesson\WordImageServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class WordImageProcessor implements WordImageProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WordImageServiceInterface $wordServices,
        private FileNameGeneratorInterface $fileNameGenerator,
        private FileManagerInterface $fileManager,
        private string $wordImageUploadDir,
        private string $wordImageUploadDirRelative,
        private Word $word
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(UploadedFile $imageFile): string
    {
        if ($this->word->getImage()) {
            $this->wordServices->removeWordImageFile($this->word);
        }

        $newFilename = $this->fileNameGenerator->generate(
            (string)$this->word->getId(),
            $imageFile->getClientOriginalName()
        );

        $imageSubPath = ltrim($this->word->getImageRelativePath(), '/');
        $targetDirectory = rtrim($this->wordImageUploadDir, '/') . '/' . $imageSubPath;

        $this->fileManager->upload($imageFile, $targetDirectory, $newFilename);

        $publicBasePath = '/' . trim($this->wordImageUploadDirRelative, '/');
        $fullRelativePath = $publicBasePath . '/' . $imageSubPath . $newFilename;

        $this->word->setImage($fullRelativePath);
        $this->entityManager->persist($this->word);
        $this->entityManager->flush();

        return $this->word->getImage();
    }
}