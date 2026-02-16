<?php

declare(strict_types=1);

namespace App\Service\AI;

use Gemini\Data\GenerationConfig;
use Gemini\Resources\GenerativeModel;

final class GeminiSdkModel implements GeminiModelInterface
{
    public function __construct(private GenerativeModel $inner)
    {
    }

    public function withGenerationConfig(GenerationConfig $config): GeminiModelInterface
    {
        $configured = $this->inner->withGenerationConfig(generationConfig: $config);

        return new self($configured);
    }

    public function generateContent(string $prompt): object
    {
        return $this->inner->generateContent($prompt);
    }
}
