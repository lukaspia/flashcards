<?php

declare(strict_types=1);


namespace App\Controller\Api;


use App\Form\UploadWordImageTypeForm;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageController extends AbstractApiController
{
    #[Route('/image/upload', name: 'image_upload', methods: ['POST'])]
    public function uploadImage(Request $request, SluggerInterface $slugger, Packages $packages): JsonResponse
    {
        if (!($this->getUser())) {
            return $this->createResponse(null, ['Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        $form = $this->createForm(UploadWordImageTypeForm::class);
        $form->handleRequest($request);
        $errors = $form->getErrors(true);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid('', true) . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('word_image_upload_dir_temp'),
                        $newFilename
                    );

                    $imageUrl = $packages->getUrl($this->getParameter('word_image_upload_dir_relative')) . 'temp/' . $newFilename;

                    return $this->createResponse(['image' => $newFilename, 'url' => $imageUrl], ['Image uploaded successfully'], Response::HTTP_OK);
                } catch (FileException $e) {
                    return $this->createResponse(null, ['Upload image error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }

        return $this->createResponse($errors, ['Something went wrong'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}