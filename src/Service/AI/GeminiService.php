<?php

declare(strict_types=1);


namespace App\Service\AI;


use App\Service\AI\AIGeneratorInterface;
use Gemini\Client;
use Gemini\Resources\GenerativeModel;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\ResponseMimeType;

class GeminiService implements AIGeneratorInterface
{
    /**
     * @var \Gemini\Client
     */
    private Client $geminiClient;

    private GenerativeModel $model;

    public function __construct(string $apiKey)
    {
        $this->geminiClient = \Gemini::client($apiKey);
        $this->model = $this->geminiClient->generativeModel(model: 'gemini-2.0-flash');
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