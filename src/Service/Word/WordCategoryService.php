<?php

declare(strict_types=1);

namespace App\Service\Word;

use App\Entity\WordCategory;
use Doctrine\ORM\EntityManagerInterface;

readonly class WordCategoryService implements WordCategoryServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return WordCategory[]
     */
    public function getAllCategories(): array
    {
        return $this->entityManager->getRepository(WordCategory::class)->findAll();
    }
}
