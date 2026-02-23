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
        private string $wordImageUploadDirTemp,
        private string $wordImageUploadDirRelative
    ) {}

    public function process(UploadedFile $imageFile, ?Word $word = null): string
    {
        if ($word === null) {
            return $this->processTemporary($imageFile);
        }

        return $this->processPermanent($imageFile, $word);
    }

    private function processTemporary(UploadedFile $imageFile): string
    {
        $extension = $imageFile->guessExtension() ?? $imageFile->getClientOriginalExtension();
        $newFilename = uniqid('temp_', true) . '.' . $extension;

        $this->fileManager->upload($imageFile, $this->wordImageUploadDirTemp, $newFilename);

        return sprintf('/%s/temp/%s', trim($this->wordImageUploadDirRelative, '/'), $newFilename);
    }

    private function processPermanent(UploadedFile $imageFile, Word $word): string
    {
        if ($word->getImage()) {
            $this->wordServices->removeWordImageFile($word);
        }

        $newFilename = $this->fileNameGenerator->generate((string)$word->getId(), $imageFile->getClientOriginalName());
        $imageSubPath = ltrim($word->getImageRelativePath(), '/');
        $targetDirectory = rtrim($this->wordImageUploadDir, '/') . '/' . $imageSubPath;

        $this->fileManager->upload($imageFile, $targetDirectory, $newFilename);

        $fullRelativePath = sprintf('/%s/%s%s',
                                    trim($this->wordImageUploadDirRelative, '/'),
                                    $imageSubPath,
                                    $newFilename
        );

        $word->setImage($fullRelativePath);
        $this->entityManager->persist($word);
        $this->entityManager->flush();

        return $word->getImage();
    }
}