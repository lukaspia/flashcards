<?php

namespace App\Service\AI;

interface AIGeneratorInterface
{
    public function generateText(string $prompt): string;

    public function generateStructuredAnswer(string $prompt, array $answerProperties): array;
}