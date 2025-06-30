<?php

declare(strict_types=1);


namespace App\Service\AI;


use Gemini\Resources\GenerativeModel;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\ResponseMimeType;

class GeminiService implements AIGeneratorInterface
{
    private GenerativeModel $model;

    public function __construct(string $apiKey)
    {
        if (empty($apiKey)) {
            throw new \InvalidArgumentException('API key cannot be empty');
        }

        try {
            $geminiClient = \Gemini::client($apiKey);
            $this->model = $geminiClient->generativeModel(model: 'gemini-2.0-flash');
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to generate text from Gemini: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param string $prompt
     * @param array $answerProperties
     * @return array
     */
    public function generateStructuredAnswer(string $prompt, array $answerProperties): array
    {
        foreach ($answerProperties as $answerProperty) {
            if (!($answerProperty instanceof Schema)) {
                throw new \InvalidArgumentException('Expected Schema instance');
            }
        }

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
    }

}