<?php

declare(strict_types=1);


namespace App\Service\Lesson;


use App\Entity\Word;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     */
    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag)
    {
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
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
}