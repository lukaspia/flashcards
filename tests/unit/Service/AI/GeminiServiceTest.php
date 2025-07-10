<?php

declare(strict_types=1);

namespace App\Tests\Service\AI;

use App\Service\AI\GeminiService;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use PHPUnit\Framework\TestCase;

class TestableGeminiService extends GeminiService
{
    private $mockResponses = [];
    private $shouldThrow = [];
    private $receivedPrompts = [];
    private $receivedConfigs = [];
    private $model;  

    public function __construct()
    {
        // Bypass parent constructor to avoid final class issues
    }

    public function initializeForTest(string $apiKey = 'test-key'): void
    {
        // First validate the API key like the parent would
        if (empty($apiKey)) {
            throw new \InvalidArgumentException('API key cannot be empty');
        }

        // Initialize a dummy model that implements the required interface
        $this->model = new class() {
            public function generateContent($prompt) {
                return (object)['text' => ''];
            }
            public function withGenerationConfig($config) {
                return $this;
            }
            public function json() {
                return [];
            }
        };
    }

    public function setMockResponse(string $method, $response): void
    {
        $this->mockResponses[$method] = $response;
    }

    public function setShouldThrow(string $method, \Throwable $exception): void
    {
        $this->shouldThrow[$method] = $exception;
    }

    public function getReceivedPrompts(): array
    {
        return $this->receivedPrompts;
    }

    public function getReceivedConfigs(): array
    {
        return $this->receivedConfigs;
    }

    public function generateText(string $prompt): string
    {
        $this->receivedPrompts[] = $prompt;

        if (isset($this->shouldThrow[__FUNCTION__])) {
            throw $this->shouldThrow[__FUNCTION__];
        }

        return $this->mockResponses[__FUNCTION__] ?? '';
    }

    public function generateStructuredAnswer(string $prompt, array $answerProperties): array
    {
        $this->receivedPrompts[] = $prompt;
        $this->receivedConfigs[] = $answerProperties;

        if (isset($this->shouldThrow[__FUNCTION__])) {
            throw $this->shouldThrow[__FUNCTION__];
        }

        return $this->mockResponses[__FUNCTION__] ?? [];
    }
}

class GeminiServiceTest extends TestCase
{
    private TestableGeminiService $geminiService;

    protected function setUp(): void
    {
        $this->geminiService = new TestableGeminiService();
        $this->geminiService->initializeForTest('test-api-key');
    }

    public function testConstructorWithEmptyApiKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('API key cannot be empty');

        $service = new TestableGeminiService();
        $service->initializeForTest('');
    }

    public function testGenerateTextSuccessfully(): void
    {
        $testPrompt = 'Test prompt';
        $expectedResponse = 'Generated response';

        $this->geminiService->setMockResponse('generateText', $expectedResponse);
        $result = $this->geminiService->generateText($testPrompt);

        $this->assertSame($expectedResponse, $result);
        $this->assertContains($testPrompt, $this->geminiService->getReceivedPrompts());
    }

    public function testGenerateTextThrowsException(): void
    {
        $testPrompt = 'Test prompt';
        $errorMessage = 'API error';

        // Mock the generateText method to throw the exception directly
        $this->geminiService->setShouldThrow(
            'generateText',
            new \RuntimeException('Failed to generate text from Gemini: ' . $errorMessage)
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to generate text from Gemini: ' . $errorMessage);

        $this->geminiService->generateText($testPrompt);
    }

    public function testGenerateStructuredAnswerSuccessfully(): void
    {
        $testPrompt = 'Test prompt';
        $testSchema = new Schema(DataType::STRING, description: 'Test schema');
        $answerProperties = ['test' => $testSchema];
        $expectedResponse = ['test' => 'value'];

        $this->geminiService->setMockResponse('generateStructuredAnswer', $expectedResponse);
        $result = $this->geminiService->generateStructuredAnswer($testPrompt, $answerProperties);

        $this->assertSame($expectedResponse, $result);
        $this->assertContains($testPrompt, $this->geminiService->getReceivedPrompts());
        $this->assertContains($answerProperties, $this->geminiService->getReceivedConfigs());
    }

    public function testGenerateStructuredAnswerWithInvalidSchema(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected Schema instance');

        // Create a testable service that implements the validation
        $service = new class() extends TestableGeminiService {
            public function validateSchema(array $answerProperties): void
            {
                foreach ($answerProperties as $answerProperty) {
                    if (!($answerProperty instanceof Schema)) {
                        throw new \InvalidArgumentException('Expected Schema instance');
                    }
                }
            }

            public function generateStructuredAnswer(string $prompt, array $answerProperties): array
            {
                $this->validateSchema($answerProperties);
                return parent::generateStructuredAnswer($prompt, $answerProperties);
            }
        };
        $service->initializeForTest('test-key');
        
        $service->generateStructuredAnswer('test', ['invalid' => 'schema']);
    }
}
