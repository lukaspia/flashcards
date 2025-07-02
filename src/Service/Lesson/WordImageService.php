<?php

declare(strict_types=1);


namespace App\Service\Lesson;


use App\Entity\Word;
use App\File\FileManagerInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

//* ENTER POINT

readonly class WordImageService implements WordImageServiceInterface
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     * @param \App\File\FileManager $fileManager
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $parameterBag,
        private FileManagerInterface $fileManager
    ) {
    }

    /**
     * @param \App\Entity\Word $word
     * @return string|null
     */
    public function getWordImageFilePath(Word $word): ?string
    {
        if (!$word->getImage()) {
            return null;
        }

        $publicDir = $this->parameterBag->get('public_dir');
        $imagePath = $word->getImage();

        return rtrim($publicDir, '/') . $imagePath;
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

        try {
            $this->entityManager->beginTransaction();

            $fileRemoved = $this->removeWordImageFile($word);

            if (!$fileRemoved) {
                throw new \RuntimeException('Failed to remove the image file');
            }

            $word->setImage(null);
            $this->entityManager->persist($word);
            $this->entityManager->flush();

            $this->entityManager->commit();
            return true;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new \RuntimeException('Failed to remove word image: ' . $e->getMessage());
        }
    }

    /**
     * @param \App\Entity\Word $word
     * @return bool
     */
    public function removeWordImageFile(Word $word): bool
    {
        if (!($wordFilePath = $this->getWordImageFilePath($word))) {
            return false;
        }

        try {
            return $this->fileManager->removeFile($wordFilePath);
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(
                sprintf('Failed to remove image file for word ID %s: %s', $word->getId(), $e->getMessage())
            );
        }
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $words
     * @return void
     */
    public function moveWordsImagesFromTemporary(Collection $words): void
    {
        if ($words->isEmpty()) {
            return;
        }

        $uploadDirTemp = $this->parameterBag->get('word_image_upload_dir_temp');
        $uploadDir = $this->parameterBag->get('word_image_upload_dir');
        $uploadDirRelative = $this->parameterBag->get('word_image_upload_dir_relative');

        foreach ($words as $word) {
            if (!($word instanceof Word)) {
                continue;
            }

            $image = $word->getImage();
            if(empty($image)) {
                continue;
            }

            $fileName = basename($image);
            $fileRelativePath = $word->getImageRelativePath() . $fileName;
            $wordFile = $uploadDir . $fileRelativePath;
            $urlFile = $uploadDirRelative . $fileRelativePath;

            if ($this->fileManager->moveFile($uploadDirTemp . $fileName, $wordFile)) {
                $word->setImage('/' . $urlFile);
                $this->entityManager->persist($word);
            }
        }

        $this->entityManager->flush();
    }
}