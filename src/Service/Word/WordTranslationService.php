<?php

declare(strict_types=1);

namespace App\Service\Word;

use App\Schema\TranslationSchema;
use App\Service\AI\AIGeneratorInterface;
use App\Service\Word\Exception\TranslationException;

readonly class WordTranslationService implements WordTranslationServiceInterface
{
    public function __construct(
        private AIGeneratorInterface $aiGenerator
    ) {
    }

    /**
     * @return array{translation: mixed}
     */
    public function translate(string $word, string $sourceLanguage, string $targetLanguage): array
    {
        $prompt = sprintf(
            'Translate the following from %s to %s: "%s" (use most popular translation) and respond set as "translation", then show example of using this translation in some sentence, and answer set as "example".',
            $sourceLanguage,
            $targetLanguage,
            $word
        );

        try {
            $result = $this->aiGenerator->generateStructuredAnswer($prompt, TranslationSchema::getSchema());
        } catch (\Throwable $e) {
            throw new TranslationException('AI translation failed', 0, $e);
        }

        return [
            'translation' => reset($result),
        ];
    }
}
