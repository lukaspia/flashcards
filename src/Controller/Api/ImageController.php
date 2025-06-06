<?php

declare(strict_types=1);


namespace App\Controller\Api;


use App\Entity\Word;
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
            $wordId = $form->get('word')->getData();

            if ($imageFile) {
                $newFilename = md5($wordId). '.' . $imageFile->guessExtension();

                try {
                    /** @var Word $word */
                    $word = $this->entityManager->getRepository(Word::class)->find($wordId);

                    if($word) {
                        if($image = $word->getImage()) {
                            $publicDir = $this->getParameter('public_dir');
                            $wordFilePath = ltrim($publicDir, '/') . $image;

                            if(is_file($wordFilePath)) {
                                unlink($wordFilePath);
                            }
                        }

                        $imageFile->move(
                            $this->getParameter('word_image_upload_dir') . $word->getImageRelativePath(),
                            $newFilename
                        );

                        $word->setImage('/' . $this->getParameter('word_image_upload_dir_relative') . $word->getImageRelativePath() . $newFilename);

                        $this->entityManager->persist($word);
                        $this->entityManager->flush();

                        $image = $word->getImage();
                    } else {
                        $imageFile->move(
                            $this->getParameter('word_image_upload_dir_temp'),
                            $newFilename
                        );

                        $image = '/' . $this->getParameter('word_image_upload_dir_relative') . 'temp/' . $newFilename;
                    }

                    return $this->createResponse(['image' => $image, 'url' => ''], ['Image uploaded successfully'], Response::HTTP_OK);
                } catch (FileException $e) {
                    return $this->createResponse(null, ['Upload image error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }

        return $this->createResponse($errors, ['Something went wrong'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}