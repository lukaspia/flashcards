<?php

declare(strict_types=1);


namespace App\Controller\Api;


use App\Entity\Lesson;
use App\Service\Lesson\LessonServices;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class LessonController extends AbstractApiController
{
    /**
     * @var \App\Service\Lesson\LessonServices
     */
    private LessonServices $lessonServices;
    /**
     * @var \Symfony\Component\Serializer\Normalizer\DenormalizerInterface
     */
    private DenormalizerInterface $denormalizer;

    public function __construct(EntityManagerInterface $entityManager, DenormalizerInterface $denormalizer, LessonServices $lessonServices)
    {
        parent::__construct($entityManager);
        $this->lessonServices = $lessonServices;
        $this->denormalizer = $denormalizer;
    }

    #[Route('/lessons', name: 'lessons', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $page = $request->query->get('page') ? (int) $request->query->get('page') : 1;
        $limit = $this->getParameter('pagination_default_limit');
        $order = ['id' => 'DESC'];

        $lessons = $this->entityManager->getRepository(Lesson::class)->findPaginatedLessons(['user' => $this->getUser()], $order, $limit, $page);

        $totalItems = $this->entityManager->getRepository(Lesson::class)->countLessonsByCriteria(['user' => $this->getUser()]);
        $totalPages = ceil($totalItems / $limit);

        return $this->createResponse(['lessons' => $lessons, 'page' => $page, 'totalItems' => $totalItems, 'totalPages' => $totalPages], [], Response::HTTP_OK, ['groups' => 'lesson:read']);
    }

    #[Route('/lesson', name: 'add_lesson', methods: ['POST'])]
    public function addLesson(Request $request): JsonResponse
    {
        $data = $request->request->all();

        try {
            $lesson = $this->denormalizer->denormalize($data, Lesson::class);

            $lesson->setUser($this->getUser());

            $this->lessonServices->addLesson($lesson);

            return $this->createResponse(['lesson' => $lesson], ['Lesson created successfully'], 201);
        } catch (ExceptionInterface|InvalidArgumentException $e) {
            return $this->createResponse(null, ['Lesson not created', $e], 400);
        }
    }

    #[Route('/lesson/{id}', name: 'remove_lesson', methods: ['DELETE'])]
    public function removeLesson(Lesson $lesson): JsonResponse
    {
        try {
            $this->lessonServices->removeLesson($lesson);

            return $this->createResponse(['lesson' => $lesson], ['Lesson remove successfully'], 201);
        } catch (\Exception $e) {
            return $this->createResponse($lesson, ['Lesson remove error',  $e], 400);
        }
    }
}