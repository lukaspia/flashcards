<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Controller\Api\WordController;
use App\Entity\WordCategory;
use App\Repository\WordCategoryRepository;
use App\Service\AI\AIGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WordControllerTest extends TestCase
{
    private WordController $controller;
    private MockObject|EntityManagerInterface $entityManager;
    private MockObject|AIGeneratorInterface $aiGeneratorService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->aiGeneratorService = $this->createMock(AIGeneratorInterface::class);
        
        $this->controller = new WordController(
            $this->entityManager,
            $this->aiGeneratorService
        );

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->method('serialize')
            ->willReturnCallback(function ($data) {
                return json_encode([
                    'status' => 'success',
                    'data' => $data['data'] ?? null,
                    'message' => $data['message'] ?? []
                ]);
            });

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('serializer')
            ->willReturn(true);
            
        $container->method('get')
            ->with('serializer')
            ->willReturn($serializer);
            
        $this->controller->setContainer($container);
    }

    public function testTranslateWithMissingRequiredFields(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode([
            'word' => 'test',
            'sourceLanguage' => 'en'
        ]));
        $request->headers->set('Content-Type', 'application/json');

        $response = $this->controller->translate($request);
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Invalid data', $data['message'][0]);
    }

    public function testTranslateSuccess(): void
    {
        $translationResult = [
            'translation' => 'test',
            'example' => 'This is a test example.'
        ];
        
        $this->aiGeneratorService->expects($this->once())
            ->method('generateStructuredAnswer')
            ->willReturn([$translationResult]);

        $requestData = [
            'word' => 'próba',
            'sourceLanguage' => 'pl',
            'targetLanguage' => 'en'
        ];
        
        $request = new Request([], [], [], [], [], [], json_encode($requestData));
        $request->headers->set('Content-Type', 'application/json');

        $response = $this->controller->translate($request);
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame('Translate successfully', $data['message'][0]);
        $this->assertSame($translationResult, $data['data']['translation']);
        $this->assertSame($requestData, $data['data']['prompt_data']);
    }

    public function testTranslateWithAiServiceError(): void
    {
        $this->aiGeneratorService->expects($this->once())
            ->method('generateStructuredAnswer')
            ->willThrowException(new \RuntimeException('AI Service unavailable'));

        $requestData = [
            'word' => 'test',
            'sourceLanguage' => 'en',
            'targetLanguage' => 'pl'
        ];
        
        $request = new Request([], [], [], [], [], [], json_encode($requestData));
        $request->headers->set('Content-Type', 'application/json');

        $response = $this->controller->translate($request);
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Something went wrong', $data['message'][0]);
    }

    public function testGetCategoriesSuccess(): void
    {
        $categories = [
            (new WordCategory())->setName('Category 1'),
            (new WordCategory())->setName('Category 2'),
        ];

        $repository = $this->createMock(WordCategoryRepository::class);
        $repository->expects($this->once())
            ->method('findAll')
            ->willReturn($categories);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(WordCategory::class)
            ->willReturn($repository);

        $response = $this->controller->getCategories();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertCount(2, $data['data']);
        $this->assertSame('Categories fetched successfully', $data['message'][0]);
    }

    public function testGetCategoriesEmpty(): void
    {
        $repository = $this->createMock(WordCategoryRepository::class);
        $repository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(WordCategory::class)
            ->willReturn($repository);

        $response = $this->controller->getCategories();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('data', $data);
        $this->assertEmpty($data['data']);
    }
}
