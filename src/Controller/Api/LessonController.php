<?php

declare(strict_types=1);


namespace App\Controller\Api;


use App\Entity\Lesson;
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
        $name = $request->request->get('name');

        $lesson = new Lesson();
        $lesson->setName($name);
        $lesson->setAddDate(new \DateTime());

        $this->entityManager->persist($lesson);
        $this->entityManager->flush();

        return $this->createResponse([$name]);
    }
}