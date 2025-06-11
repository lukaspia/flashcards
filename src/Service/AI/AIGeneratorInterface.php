<?php

namespace App\Service\AI;

interface AIGeneratorInterface
{
    public function generateText(string $prompt): string;
}