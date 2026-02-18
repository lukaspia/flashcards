<?php

declare(strict_types=1);

namespace App\Service\AI;

use Gemini\Data\GenerationConfig;
use Gemini\Resources\GenerativeModel;

/**
 *
 */
final class GeminiSdkModel implements GeminiModelInterface
{
    /**
     * @param \Gemini\Resources\GenerativeModel $inner
     */
    public function __construct(private GenerativeModel $inner)
    {
    }

    /**
     * @param \Gemini\Data\GenerationConfig $config
     * @return \App\Service\AI\GeminiModelInterface
     */
    public function withGenerationConfig(GenerationConfig $config): GeminiModelInterface
    {
        $configured = $this->inner->withGenerationConfig(generationConfig: $config);

        return new self($configured);
    }

    /**
     * @param string $prompt
     * @return object|\Gemini\Responses\GenerativeModel\GenerateContentResponse
     */
    public function generateContent(string $prompt): object
    {
        return $this->inner->generateContent($prompt);
    }
}
