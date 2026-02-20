<?php

declare(strict_types=1);


namespace App\Controller\Api;


use App\DTO\UploadImageDTO;
use App\Entity\Word;
use App\Factory\WordImageProcessorFactoryInterface;
use App\File\FileNameGeneratorInterface;
use App\Form\UploadWordImageTypeForm;
use App\Service\Lesson\WordImageServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
    public function uploadImage(Request $request): JsonResponse
    {
        $dto = new UploadImageDTO(
            $request->request->get('word'),
            $request->files->get('image')
        );

        $this->validateDto($dto);

        $newFilename = $this->fileNameGenerator->generate(
            (string)$dto->wordId,
            $dto->image->getClientOriginalName()
        );

        $processor = $this->wordImageProcessorFactory->createProcessor((int)$dto->wordId);
        $image = $processor->process($dto->image, $newFilename);

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
    public function deleteImage(Word $word): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->createResponse(
                null,
                ['Authentication required.'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        if($word->getLesson()->getUser() !== $user) {
            return $this->createResponse(
                null,
                ['You do not have permission to delete this image.'],
                Response::HTTP_FORBIDDEN
            );
        }

        try {
            if ($this->wordServices->removeWordImage($word)) {
                $this->logger->info('Image deleted successfully', [
                    'wordId' => $word->getId(),
                    'userId' => $user->getId()
                ]);
                return $this->createResponse(null, ['Image deleted successfully'], Response::HTTP_OK);
            }

            $this->logger->warning('Image not found for deletion', [
                'wordId' => $word->getId(),
                'userId' => $user->getId()
            ]);
            return $this->createResponse(null, ['Image not found or can\'t be remove'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete image', [
                'wordId' => $word->getId(),
                'userId' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            return $this->createResponse(
                null,
                ['Failed to delete image'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}