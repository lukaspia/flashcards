<?php

declare(strict_types=1);

namespace App\Service\Word;

use App\Entity\WordCategory;

interface WordCategoryServiceInterface
{
    /**
     * @return array<int, array{id: int, name: string}>
     */
    public function getAllCategories(): array;

    /**
     * @param string $name
     * @return \App\Entity\WordCategory
     */
    public function createCategory(string $name): WordCategory;
}
