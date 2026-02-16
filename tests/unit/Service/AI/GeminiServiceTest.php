<?php

declare(strict_types=1);

namespace App\Tests\Service\AI;

use App\Service\AI\GeminiClientInterface;
use App\Service\AI\GeminiModelInterface;
use App\Service\AI\GeminiService;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class GeminiServiceTest extends TestCase
{
    private GeminiClientInterface&MockObject $client;

    private GeminiModelInterface&MockObject $model;

    private LoggerInterface&MockObject $logger;

    private GeminiService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(GeminiClientInterface::class);
        $this->model = $this->createMock(GeminiModelInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->client
            ->method('generativeModel')
            ->with('test-model')
            ->willReturn($this->model);

        $this->service = new GeminiService($this->client, $this->logger, 'test-model');
    }

    public function testGenerateTextSuccessfully(): void
    {
        $prompt = 'Test prompt';
        $expectedText = 'Generated response';

        $response = new class($expectedText) {
            public function __construct(private string $text) {}

            public function text(): string
            {
                return $this->text;
            }
        };

        $this->model
            ->expects($this->once())
            ->method('generateContent')
            ->with($prompt)
            ->willReturn($response);

        $result = $this->service->generateText($prompt);

        $this->assertSame($expectedText, $result);
    }

    public function testGenerateTextThrowsExceptionAndLogsError(): void
    {
        $prompt = 'Test prompt';
        $innerException = new \RuntimeException('API error');

        $this->model
            ->expects($this->once())
            ->method('generateContent')
            ->with($prompt)
            ->willThrowException($innerException);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Failed to generate text from Gemini',
                $this->callback(function (array $context) use ($prompt, $innerException) {
                    return $context['exception'] === $innerException
                        && str_contains($context['prompt'], $prompt);
                })
            );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to generate text from Gemini: API error');

        $this->service->generateText($prompt);
    }

    public function testGenerateStructuredAnswerSuccessfully(): void
    {
        $prompt = 'Test prompt';
        $schema = new Schema(DataType::STRING, description: 'Test schema');
        $answerProperties = ['test' => $schema];
        $expected = [['test' => 'value']];

        $configuredModel = $this->createMock(GeminiModelInterface::class);

        $this->model
            ->expects($this->once())
            ->method('withGenerationConfig')
            ->with($this->isInstanceOf(GenerationConfig::class))
            ->willReturn($configuredModel);

        $response = new class($expected) {
            public function __construct(private array $json) {}

            public function json(): array
            {
                return $this->json;
            }
        };

        $configuredModel
            ->expects($this->once())
            ->method('generateContent')
            ->with($prompt)
            ->willReturn($response);

        $result = $this->service->generateStructuredAnswer($prompt, $answerProperties);

        $this->assertSame($expected, $result);
    }

    public function testGenerateStructuredAnswerWithInvalidSchemaThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected Schema instance for answer property "invalid"');

        $this->service->generateStructuredAnswer('prompt', ['invalid' => 'schema']);
    }

    public function testConstructorFailureLogsAndThrows(): void
    {
        $client = $this->createMock(GeminiClientInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $exception = new \RuntimeException('init error');

        $client
            ->expects($this->once())
            ->method('generativeModel')
            ->with('broken-model')
            ->willThrowException($exception);

        $logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Failed to initialize Gemini service',
                $this->callback(function (array $context) use ($exception) {
                    return $context['exception'] === $exception
                        && $context['model'] === 'broken-model';
                })
            );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to initialize Gemini service');

        new GeminiService($client, $logger, 'broken-model');
    }
}
