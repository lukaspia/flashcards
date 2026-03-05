<?php

declare(strict_types=1);

namespace App\Service\Word;

interface WordTranslationServiceInterface
{
    /**
     * @return array{translation: mixed}
     */
    public function translate(string $word, string $sourceLanguage, string $targetLanguage): array;
}
