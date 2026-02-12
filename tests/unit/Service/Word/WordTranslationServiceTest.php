<?php

declare(strict_types=1);

namespace App\Tests\Service\Word;

use App\Schema\TranslationSchema;
use App\Service\AI\AIGeneratorInterface;
use App\Service\Word\Exception\TranslationException;
use App\Service\Word\WordTranslationService;
use PHPUnit\Framework\TestCase;

class WordTranslationServiceTest extends TestCase
{
    public function testTranslateBuildsPromptCallsAiAndReturnsFirstResult(): void
    {
        $word = 'Test';
        $sourceLanguage = 'en';
        $targetLanguage = 'pl';

        $expectedPrompt = sprintf(
            'Translate the following from %s to %s: "%s" (use most popular translation) and respond set as "translation", then show example of using this translation in some sentence, and answer set as "example".',
            $sourceLanguage,
            $targetLanguage,
            $word
        );

        $aiGenerator = $this->createMock(AIGeneratorInterface::class);

        $aiResult = [
            [
                'translation' => 'tłumaczenie',
                'example' => 'Przykładowe zdanie.',
            ],
            [
                'translation' => 'inne tłumaczenie',
                'example' => 'Inne zdanie.',
            ],
        ];

        $aiGenerator->expects($this->once())
            ->method('generateStructuredAnswer')
            ->with(
                $expectedPrompt,
                TranslationSchema::getSchema()
            )
            ->willReturn($aiResult);

        $service = new WordTranslationService($aiGenerator);

        $result = $service->translate($word, $sourceLanguage, $targetLanguage);

        $this->assertSame([
            'translation' => $aiResult[0],
        ], $result);
    }

    public function testTranslateWrapsExceptionsInTranslationException(): void
    {
        $word = 'Test';
        $sourceLanguage = 'en';
        $targetLanguage = 'pl';

        $aiGenerator = $this->createMock(AIGeneratorInterface::class);
        $aiGenerator->expects($this->once())
            ->method('generateStructuredAnswer')
            ->willThrowException(new \RuntimeException('AI error'));

        $service = new WordTranslationService($aiGenerator);

        $this->expectException(TranslationException::class);

        $service->translate($word, $sourceLanguage, $targetLanguage);
    }
}
