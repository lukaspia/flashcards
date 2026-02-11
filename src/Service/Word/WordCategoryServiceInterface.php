<?php

declare(strict_types=1);

namespace App\Service\Word;

use App\Entity\WordCategory;

interface WordCategoryServiceInterface
{
    /**
     * @return WordCategory[]
     */
    public function getAllCategories(): array;
}
