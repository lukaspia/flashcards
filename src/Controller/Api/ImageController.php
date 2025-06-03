<?php

declare(strict_types=1);


namespace App\Controller\Api;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class ImageController extends AbstractApiController
{
    #[Route('/image/upload', name: 'image_upload', methods: ['POST'])]
    public function uploadImage(): JsonResponse
    {
        return $this->createResponse(['image' => 0], ['Image uploaded successfully'], Response::HTTP_OK);
    }
}