<?php

declare(strict_types=1);

namespace App\Service\Word;

interface WordCategoryServiceInterface
{
    /**
     * @return array<int, array{id: int, name: string}>
     */
    public function getAllCategories(): array;
}
