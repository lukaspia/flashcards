<?php

declare(strict_types=1);


namespace App\Controller\Api;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

abstract class AbstractApiController extends AbstractController
{
    protected EntityManagerInterface $entityManager;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param mixed|null $data
     * @param array $messages
     * @param int $statusCode
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function createResponse(mixed $data = null, array $messages = [], int $statusCode = Response::HTTP_OK): JsonResponse
    {
        $status = 'error';
        if($statusCode >= 200 && $statusCode < 299) {
            $status = 'success';
        }

        $response = [
            'status' => $status,
            'data' => $data,
            'message' => $messages
        ];

        return $this->json($response, $statusCode, [], [ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER=>function ($obj){return $obj->getId();}]);
    }
}