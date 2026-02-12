<?php

declare(strict_types=1);


namespace App\Controller\Api;


use App\Service\Word\Exception\TranslationException;
use App\Service\Word\WordCategoryServiceInterface;
use App\Service\Word\WordTranslationServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class WordController extends AbstractApiController
{
    public function __construct(
        EntityManagerInterface $entityManager,
        private readonly WordTranslationServiceInterface $wordTranslationService,
        private readonly WordCategoryServiceInterface $wordCategoryService
    ) {
        parent::__construct($entityManager);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/words/translate', name: 'word_translate', methods: ['POST'])]
    public function translate(Request $request): JsonResponse
    {
        $data = $request->toArray();

        if (!isset($data['word'], $data['sourceLanguage'], $data['targetLanguage'])) {
            return $this->createResponse(['prompt_data' => $data],
                                         ['Invalid data. "word", "sourceLanguage", "targetLanguage" is required.'],
                                         Response::HTTP_BAD_REQUEST);
        }

        try {
            $word = trim($data['word']);
            $sourceLanguage = trim($data['sourceLanguage']);
            $targetLanguage = trim($data['targetLanguage']);

            $result = $this->wordTranslationService->translate($word, $sourceLanguage, $targetLanguage);
        } catch (TranslationException $e) {
            return $this->createResponse(['prompt_data' => $data],
                                         ['Something went wrong.' . $e->getMessage()],
                                         Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->createResponse(['translation' => $result['translation'], 'prompt_data' => $data],
                                     ['Translate successfully'],
                                     Response::HTTP_OK);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/words/categories', name: 'word_categories', methods: ['GET'])]
    public function getCategories(): JsonResponse
    {
        $categories = $this->wordCategoryService->getAllCategories();

        return $this->createResponse($categories, ['Categories fetched successfully'], Response::HTTP_OK);
    }
}