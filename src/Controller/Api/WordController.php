<?php

declare(strict_types=1);


namespace App\Controller\Api;


use App\DTO\TranslateWordDTO;
use App\Service\Word\WordCategoryServiceInterface;
use App\Service\Word\WordTranslationServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;


class WordController extends AbstractApiController
{
    public function __construct(
        private readonly WordTranslationServiceInterface $wordTranslationService,
        private readonly WordCategoryServiceInterface $wordCategoryService,
        private readonly SerializerInterface $serializer
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/words/translate', name: 'word_translate', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function translate(Request $request): JsonResponse
    {
        /** @var TranslateWordDTO $dto */
        $dto = $this->serializer->denormalize($request->toArray(), TranslateWordDTO::class);

        $this->validateDto($dto);

        $result = $this->wordTranslationService->translate(
            trim($dto->word),
            $dto->sourceLanguage,
            $dto->targetLanguage
        );

        return $this->createResponse(
            [
                'translation' => $result['translation'],
                'prompt_data' => (array)$dto
            ],
            ['Translated successfully'],
            Response::HTTP_OK
        );
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