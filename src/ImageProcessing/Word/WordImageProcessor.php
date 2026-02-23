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

/**
 *
 */
readonly class WordImageProcessor implements WordImageProcessorInterface
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \App\Service\Lesson\WordImageServiceInterface $wordServices
     * @param \App\File\FileNameGeneratorInterface $fileNameGenerator
     * @param \App\File\FileManagerInterface $fileManager
     * @param string $wordImageUploadDir
     * @param string $wordImageUploadDirTemp
     * @param string $wordImageUploadDirRelative
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WordImageServiceInterface $wordServices,
        private FileNameGeneratorInterface $fileNameGenerator,
        private FileManagerInterface $fileManager,
        private string $wordImageUploadDir,
        private string $wordImageUploadDirTemp,
        private string $wordImageUploadDirRelative
    ) {}

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $imageFile
     * @param \App\Entity\Word|null $word
     * @return string
     */
    public function process(UploadedFile $imageFile, ?Word $word = null): string
    {
        return match (null === $word) {
            true => $this->processTemporary($imageFile),
            false => $this->processPermanent($imageFile, $word),
        };
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $imageFile
     * @return string
     */
    private function processTemporary(UploadedFile $imageFile): string
    {
        $extension = $imageFile->guessExtension() ?? $imageFile->getClientOriginalExtension();
        $newFilename = uniqid('temp_', true) . '.' . $extension;

        $this->fileManager->upload($imageFile, $this->wordImageUploadDirTemp, $newFilename);

        $base = trim($this->wordImageUploadDirRelative, '/');
        return sprintf('/%s/temp/%s', $base, $newFilename);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $imageFile
     * @param \App\Entity\Word $word
     * @return string
     */
    private function processPermanent(UploadedFile $imageFile, Word $word): string
    {
        if ($word->getImage()) {
            $this->wordServices->removeWordImageFile($word);
        }

        $newFilename = $this->fileNameGenerator->generate(
            (string)$word->getId(),
            $imageFile->getClientOriginalName()
        );

        $imageSubPath = ltrim($word->getImageRelativePath(), '/');
        $targetDirectory = sprintf('%s/%s', rtrim($this->wordImageUploadDir, '/'), $imageSubPath);

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