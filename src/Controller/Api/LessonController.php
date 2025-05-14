<?php

declare(strict_types=1);


namespace App\Controller\Api;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LessonController extends AbstractApiController
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
    }

    #[Route('/lesson', name: 'lesson', methods: ['POST'])]
    public function addLesson(Request $request): JsonResponse
    {
        return $this->createResponse([]);
    }
}