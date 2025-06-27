<?php

declare(strict_types=1);


namespace App\Controller\Api;


use App\Entity\Word;
use App\Factory\WordImageProcessorFactoryInterface;
use App\File\FileNameGeneratorInterface;
use App\Form\UploadWordImageTypeForm;
use App\Service\Lesson\WordImageServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class ImageController extends AbstractApiController
{
    public function __construct(
        EntityManagerInterface $entityManager,
        private readonly WordImageServiceInterface $wordServices,
        private readonly WordImageProcessorFactoryInterface $wordImageProcessorFactory,
        public readonly FileNameGeneratorInterface $fileNameGenerator
    ) {
        parent::__construct($entityManager);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/image/upload', name: 'image_upload', methods: ['POST'])]
    public function uploadImage(Request $request): JsonResponse
    {
        if (!($this->getUser())) {
            return $this->createResponse(null, ['Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        $form = $this->createForm(UploadWordImageTypeForm::class);
        $form->handleRequest($request);
        $errors = $form->getErrors(true);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            $wordId = $form->get('word')->getData();

            if ($imageFile) {
                $newFilename = $this->fileNameGenerator->generate((string)$wordId, $imageFile->getClientOriginalName());

                try {
                    $processor = $this->wordImageProcessorFactory->createProcessor((int)$wordId);
                    $image = $processor->process($imageFile, $newFilename);

                    return $this->createResponse(['image' => $image, 'url' => ''],
                                                 ['Image uploaded successfully'],
                                                 Response::HTTP_OK);
                } catch (FileException $e) {
                    return $this->createResponse(
                        null,
                        ['Upload image error: ' . $e->getMessage()],
                        Response::HTTP_INTERNAL_SERVER_ERROR
                    );
                }
            }
        }

        return $this->createResponse($errors, ['Something went wrong'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param \App\Entity\Word $word
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/image/{id}', name: 'image_delete', methods: ['DELETE'])]
    public function deleteImage(Word $word): JsonResponse
    {
        if ($this->wordServices->removeWordImage($word)) {
            return $this->createResponse(null, ['Image deleted successfully'], Response::HTTP_OK);
        }

        return $this->createResponse(null, ['Image not found or can\'t be remove'], Response::HTTP_NOT_FOUND);
    }
}