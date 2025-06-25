<?php

declare(strict_types=1);


namespace App\Service\Lesson;


use App\Entity\Word;
use App\Utils\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\Common\Collections\Collection;

class WordServices
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;
    /**
     * @var \App\Utils\FileManager
     */
    private FileManager $fileManager;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     * @param \App\Utils\FileManager $fileManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        FileManager $fileManager
    ) {
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->fileManager = $fileManager;
    }

    /**
     * @param \App\Entity\Word $word
     * @return string|null
     */
    public function getWordImageFilePath(Word $word): ?string
    {
        if ($image = $word->getImage()) {
            $publicDir = $this->parameterBag->get('public_dir');
            return rtrim($publicDir, '/') . $image;
        }

        return null;
    }

    /**
     * @param \App\Entity\Word $word
     * @return bool
     */
    public function removeWordImage(Word $word): bool
    {
        if ($word->getImage()) {
            $this->removeWordImageFile($word);

            $word->setImage(null);

            $this->entityManager->persist($word);
            $this->entityManager->flush();

            return true;
        }

        return false;
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

        if (is_file($wordFilePath)) {
            unlink($wordFilePath);
            return true;
        }

        return false;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $words
     * @return void
     */
    public function moveWordsImagesFromTemporary(Collection $words): void
    {
        $uploadDirTemp = $this->parameterBag->get('word_image_upload_dir_temp');
        $uploadDir = $this->parameterBag->get('word_image_upload_dir');
        $uploadDirRelative = $this->parameterBag->get('word_image_upload_dir_relative');

        foreach ($words as $word) {
            if (!($word instanceof Word)) {
                continue;
            }

            if ($image = $word->getImage()) {
                $fileName = basename($image);
                $fileRelativePath = $word->getImageRelativePath() . $fileName;
                $wordFile = $uploadDir . $fileRelativePath;
                $urlFile = $uploadDirRelative . $fileRelativePath;

                if ($this->fileManager->moveFile($uploadDirTemp . $fileName, $wordFile)) {
                    $word->setImage('/' . $urlFile);
                    $this->entityManager->persist($word);
                }
            }
        }

        $this->entityManager->flush();
    }
}