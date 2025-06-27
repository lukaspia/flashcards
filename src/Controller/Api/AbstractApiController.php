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

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(protected readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param mixed|null $data
     * @param array $messages
     * @param int $statusCode
     * @param array $context
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function createResponse(
        mixed $data = null,
        array $messages = [],
        int $statusCode = Response::HTTP_OK,
        array $context = []
    ): JsonResponse {
        $status = ($statusCode >= 200 && $statusCode < 300) ? 'success' : 'error';

        $response = [
            'status' => $status,
            'data' => $data,
            'message' => $messages
        ];

        $context = array_merge($context, [
            ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($obj): mixed {
                return $obj->getId();
            }
        ]);

        return $this->json($response, $statusCode, [], $context);
    }
}