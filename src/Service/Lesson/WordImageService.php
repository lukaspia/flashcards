<?php

declare(strict_types=1);


namespace App\Service\Lesson;


use App\Entity\Word;
use App\File\FileManagerInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

readonly class WordImageService implements WordImageServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FileManagerInterface $fileManager,
        private string $publicDir,
        private string $uploadDirTemp,
        private string $uploadDir,
        private string $uploadDirRelative
    ) {
    }

    /**
     * @param \App\Entity\Word $word
     * @return string|null
     */
    public function getWordImageFilePath(Word $word): ?string
    {
        $imagePath = $word->getImage();
        if (!$imagePath) {
            return null;
        }

        return rtrim($this->publicDir, '/') . '/' . ltrim($imagePath, '/');
    }

    /**
     * @param \App\Entity\Word $word
     * @return bool
     */
    public function removeWordImage(Word $word): bool
    {
        if (!$word->getImage()) {
            return false;
        }
        return $this->entityManager->wrapInTransaction(function () use ($word) {
            if (!$this->removeWordImageFile($word)) {
                throw new \RuntimeException('Failed to remove the image file');
            }

            $word->setImage(null);
            $this->entityManager->flush();

            return true;
        });
    }

    /**
     * @param \App\Entity\Word $word
     * @return bool
     */
    public function removeWordImageFile(Word $word): bool
    {
        $filePath = $this->getWordImageFilePath($word);
        if (!$filePath) {
            return false;
        }

        return $this->fileManager->removeFile($filePath);
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<int, Word> $words
     * @return void
     */
    public function moveWordsImagesFromTemporary(Collection $words): void
    {
        $hasChanges = false;

        foreach ($words as $word) {
            if (!$this->shouldProcessWordImage($word)) {
                continue;
            }

            $imageName = basename($word->getImage());
            $relativeDir = $this->generateRelativePath($word);
            $relativeDestination = $relativeDir . $imageName;

            $sourcePath = rtrim($this->uploadDirTemp, '/') . '/' . ltrim($imageName, '/');
            $destinationPath = rtrim($this->uploadDir, '/') . '/' . ltrim($relativeDestination, '/');

            if ($this->fileManager->moveFile($sourcePath, $destinationPath)) {
                $word->setImage('/' . rtrim($this->uploadDirRelative, '/') . '/' . ltrim($relativeDestination, '/'));
                $hasChanges = true;
            }
        }

        if ($hasChanges) {
            $this->entityManager->flush();
        }
    }

    /**
     * @param \App\Entity\Word $word
     * @return string
     */
    public function generateRelativePath(Word $word): string
    {
        $lesson = $word->getLesson();
        if (!$lesson) {
            throw new \RuntimeException(sprintf('Word ID %s has no lesson assigned.', $word->getId()));
        }

        $user = $lesson->getUser();
        if (!$user) {
            throw new \RuntimeException(sprintf('Lesson ID %s has no user assigned.', $lesson->getId()));
        }

        return sprintf('%d/%d/', $user->getId(), $lesson->getId());
    }

    /**
     * @param \App\Entity\Word $word
     * @return bool
     */
    private function shouldProcessWordImage(Word $word): bool
    {
        $image = $word->getImage();
        if (empty($image)) {
            return false;
        }

        $targetPrefix = '/' . ltrim($this->uploadDirRelative, '/');

        return !str_starts_with($image, $targetPrefix);
    }
}