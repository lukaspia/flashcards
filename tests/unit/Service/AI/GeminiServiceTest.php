<?php

namespace App\Tests\Service\AI;

use App\Service\AI\GeminiClientInterface;
use App\Service\AI\GeminiModelInterface;
use App\Service\AI\GeminiService;
use Gemini\Data\Schema;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class GeminiServiceTest extends TestCase
{
    private MockObject|GeminiClientInterface $client;
    private MockObject|GeminiModelInterface $model;
    private MockObject|LoggerInterface $logger;
    private GeminiService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(GeminiClientInterface::class);
        $this->model = $this->createMock(GeminiModelInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->client->method('generativeModel')->willReturn($this->model);

        $this->service = new GeminiService($this->client, $this->logger);
    }

    public function testGenerateTextSuccess(): void
    {
        $prompt = 'Cześć Gemini!';
        $expectedText = 'Witaj Nieznajomy!';

        $response = new class($expectedText) {
            public function __construct(private string $t)
            {
            }

            public function text(): string
            {
                return $this->t;
            }
        };

        $this->model->expects($this->once())
            ->method('generateContent')
            ->with($prompt)
            ->willReturn($response);

        $result = $this->service->generateText($prompt);

        $this->assertEquals($expectedText, $result);
    }

    public function testGenerateStructuredAnswerConfiguresJsonMode(): void
    {
        $prompt = 'Przetłumacz: Apple';
        $properties = ['translation' => $this->createMock(Schema::class)];
        $expectedData = [['translation' => 'Jabłko']];

        $structuredModel = $this->createMock(GeminiModelInterface::class);

        $this->model->method('withGenerationConfig')->willReturn($structuredModel);

        $response = new class($expectedData) {
            public function __construct(private array $j)
            {
            }

            public function json(): array
            {
                return $this->j;
            }
        };

        $structuredModel->expects($this->once())
            ->method('generateContent')
            ->with($prompt)
            ->willReturn($response);

        $result = $this->service->generateStructuredAnswer($prompt, $properties);

        $this->assertEquals($expectedData, $result);
    }
}