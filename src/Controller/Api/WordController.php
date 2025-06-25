<?php

declare(strict_types=1);


namespace App\Controller\Api;


use App\Entity\WordCategory;
use App\Service\AI\AIGeneratorInterface;
use App\Service\AI\GeminiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;

class WordController extends AbstractApiController
{
    /**
     * @var \App\Service\AI\GeminiService
     */
    private GeminiService $geminiService;

    public function __construct(EntityManagerInterface $entityManager, AIGeneratorInterface $geminiService)
    {
        parent::__construct($entityManager);

        $this->geminiService = $geminiService;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/word/translate', name: 'word_translate', methods: ['POST'])]
    public function translate(Request $request): JsonResponse
    {
        $data = $request->toArray();

        if (!isset($data['word'], $data['sourceLanguage'], $data['targetLanguage'])) {
            return $this->createResponse(['prompt_data' => $data],
                                         ['Invalid data. "word", "sourceLanguage", "targetLanguage" is required.'],
                                         Response::HTTP_BAD_REQUEST);
        }

        $prompt = sprintf(
            'Translate this from %s to %s: "%s" (use most popular translation) and answer set as "translation", then show example of using this translation in some sentence, and answer set as "example".',
            $data['sourceLanguage'],
            $data['targetLanguage'],
            $data['word']
        );
        try {
            $result = $this->geminiService->generateStructuredAnswer(
                $prompt,
                ['translation' => new Schema(type: DataType::STRING), 'example' => new Schema(type: DataType::STRING)]
            );
        } catch (\Exception $e) {
            return $this->createResponse(['prompt_data' => $data],
                                         ['Something went wrong.' . $e->getMessage()],
                                         Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->createResponse(['translation' => reset($result), 'prompt_data' => $data],
                                     ['Translate successfully'],
                                     Response::HTTP_OK);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/word/get-categories', name: 'word_get_categories', methods: ['GET'])]
    public function getCategories(): JsonResponse
    {
        $categories = $this->entityManager->getRepository(WordCategory::class)->findAll();

        return $this->createResponse($categories, ['Categories fetched successfully'], Response::HTTP_OK);
    }
}