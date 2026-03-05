<?php

namespace App\Service\AI;

interface AIGeneratorInterface
{
    public function generateText(string $prompt): string;

    /**
     * @param array<string, mixed> $answerProperties
     * @return array<int, array<string, mixed>>
     */
    public function generateStructuredAnswer(string $prompt, array $answerProperties): array;
}