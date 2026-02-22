<?php

declare(strict_types=1);


namespace App\Controller\Api;


use App\DTO\UploadImageDTO;
use App\Entity\Word;
use App\Factory\WordImageProcessorFactoryInterface;
use App\File\FileNameGeneratorInterface;
use App\Form\UploadWordImageTypeForm;
use App\Repository\WordRepository;
use App\Security\Voter\WordVoter;
use App\Service\Lesson\WordImageServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class ImageController extends AbstractApiController
{
    public function __construct(
        private readonly WordImageServiceInterface $wordServices,
        private readonly WordImageProcessorFactoryInterface $wordImageProcessorFactory,
        private readonly FileNameGeneratorInterface $fileNameGenerator,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/images/upload', name: 'image_upload', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function uploadImage(Request $request, WordRepository $wordRepository): JsonResponse
    {
        $dto = new UploadImageDTO(
            $request->request->get('word'),
            $request->files->get('image')
        );

        $this->validateDto($dto);

        $word = $dto->wordId > 0 ? $wordRepository->find($dto->wordId) : null;

        if ($dto->wordId > 0) {
            if (!$word) {
                throw $this->createNotFoundException('Word with ID ' . $dto->wordId . ' could not be found.');
            }

            $this->denyAccessUnlessGranted(WordVoter::UPLOAD_IMAGE, $word);
        }

        $processor = $this->wordImageProcessorFactory->createProcessor($word);
        $image = $processor->process($dto->image);

        return $this->createResponse(
            ['image' => $image, 'url' => ''],
            ['Image uploaded successfully']
        );
    }

    /**
     * @param \App\Entity\Word $word
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/images/{id}', name: 'image_delete', methods: ['DELETE'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function deleteImage(Word $word): JsonResponse
    {
        $this->denyAccessUnlessGranted(WordVoter::DELETE_IMAGE, $word);

        $result = $this->wordServices->removeWordImage($word);

        if (!$result) {
            return $this->createResponse(
                null,
                ['Image not found or already removed'],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->createResponse(null, ['Image deleted successfully']);
    }
}