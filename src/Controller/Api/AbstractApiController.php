<?php

declare(strict_types=1);


namespace App\Controller\Api;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 *
 */
abstract class AbstractApiController extends AbstractController
{
    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    protected ValidatorInterface $validator;

    /**
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @return void
     */
    #[Required]
    public function setValidator(ValidatorInterface $validator): void
    {
        $this->validator = $validator;
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
                return method_exists($obj, 'getId') ? $obj->getId() : null;
            }
        ]);

        return $this->json($response, $statusCode, [], $context);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     */
    protected function getPaginationParams(Request $request): array
    {
        $defaultLimit = (int)$this->getParameter('pagination_default_limit');

        return [
            'page' => max(1, $request->query->getInt('page', 1)),
            'limit' => max(1, min(100, $request->query->getInt('limit', $defaultLimit))),
        ];
    }

    /**
     * @param object $dto
     * @return void
     */
    protected function validateDto(object $dto): void
    {
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            throw new ValidationFailedException($dto, $errors);
        }
    }
}