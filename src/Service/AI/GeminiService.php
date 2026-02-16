<?php

declare(strict_types=1);


namespace App\Service\AI;

use Gemini\Client;
use Gemini\Resources\GenerativeModel;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\ResponseMimeType;

readonly class GeminiService implements AIGeneratorInterface
{
    private const DEFAULT_MODEL = 'gemini-2.5-flash-lite';

    private GenerativeModel $model;

    public function __construct(Client $geminiClient, string $modelName = self::DEFAULT_MODEL)
    {
        try {
            $this->model = $geminiClient->generativeModel(model: $modelName);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to initialize Gemini service', 0, $e);
        }
    }

    /**
     * @param string $prompt
     * @return string
     */
    public function generateText(string $prompt): string
    {
        try {
            $result = $this->model->generateContent($prompt);

            return $result->text();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to generate text from Gemini: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param string $prompt
     * @param array<string, Schema> $answerProperties
     * @return array<mixed>
     */
    public function generateStructuredAnswer(string $prompt, array $answerProperties): array
    {
        foreach ($answerProperties as $answerProperty) {
            if (!($answerProperty instanceof Schema)) {
                throw new \InvalidArgumentException('Expected Schema instance');
            }
        }

        try {
            $result = $this->model->withGenerationConfig(
                generationConfig: new GenerationConfig(
                                      responseMimeType: ResponseMimeType::APPLICATION_JSON,
                                      responseSchema:   new Schema(
                                                            type:  DataType::ARRAY,
                                                            items: new Schema(
                                                                       type:       DataType::OBJECT,
                                                                       properties: $answerProperties,
                                                                       required:   array_keys($answerProperties),
                                                                   )
                                                        )
                                  )
            )->generateContent($prompt);

            return $result->json();
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                'Failed to generate structured answer from Gemini: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }
}