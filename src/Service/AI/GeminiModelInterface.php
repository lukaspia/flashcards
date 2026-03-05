<?php

declare(strict_types=1);

namespace App\Service\AI;

use Gemini\Data\GenerationConfig;

interface GeminiModelInterface
{
    public function withGenerationConfig(GenerationConfig $config): self;

    /**
     * @return object Response object exposing text()/json() depending on usage
     */
    public function generateContent(string $prompt): object;
}
