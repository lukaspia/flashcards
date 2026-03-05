<?php

namespace App\Tests\Service\Word;

use App\Service\AI\AIGeneratorInterface;
use App\Service\Word\Exception\TranslationException;
use App\Service\Word\WordTranslationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WordTranslationServiceTest extends TestCase
{
    private MockObject|AIGeneratorInterface $aiGenerator;
    private WordTranslationService $service;

    protected function setUp(): void
    {
        $this->aiGenerator = $this->createMock(AIGeneratorInterface::class);
        $this->service = new WordTranslationService($this->aiGenerator);
    }

    public function testTranslateReturnsFirstElementOfAiResult(): void
    {
        $word = 'apple';
        $source = 'English';
        $target = 'Polish';

        $aiResponse = [
            [
                'translation' => 'jabłko',
                'example' => 'Lubię jeść czerwone jabłka.'
            ]
        ];

        $this->aiGenerator->expects($this->once())
            ->method('generateStructuredAnswer')
            ->with(
                $this->callback(fn(string $prompt) =>
                    str_contains($prompt, $word) &&
                    str_contains($prompt, $source) &&
                    str_contains($prompt, $target)
                ),
                $this->isType('array')
            )
            ->willReturn($aiResponse);

        $result = $this->service->translate($word, $source, $target);

        $this->assertArrayHasKey('translation', $result);
        $this->assertEquals($aiResponse[0], $result['translation']);
    }

    public function testTranslateThrowsTranslationExceptionOnAiFailure(): void
    {
        $this->aiGenerator->method('generateStructuredAnswer')
            ->willThrowException(new \RuntimeException('API Error'));

        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage('AI translation failed');

        $this->service->translate('test', 'en', 'pl');
    }
}