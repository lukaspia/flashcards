<?php

declare(strict_types=1);


namespace App\Controller\Api;


use App\Entity\Lesson;
use App\Service\Lesson\LessonServices;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LessonController extends AbstractApiController
{
    private const LESSON_READ_GROUP = 'lesson:read';
    private const DEFAULT_PAGINATION_LIMIT_PARAM = 'pagination_default_limit';

    /**
     * @var \App\Service\Lesson\LessonServices
     */
    private LessonServices $lessonServices;
    /**
     * @var \Symfony\Component\Serializer\Normalizer\DenormalizerInterface
     */
    private DenormalizerInterface $denormalizer;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        DenormalizerInterface $denormalizer,
        LessonServices $lessonServices,
        LoggerInterface $logger
    ) {
        parent::__construct($entityManager);
        $this->lessonServices = $lessonServices;
        $this->denormalizer = $denormalizer;
        $this->logger = $logger;
    }

    #[Route('/lessons', name: 'lessons', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $page = $request->query->get('page') ? (int)$request->query->get('page') : 1;
        $limit = $this->getParameter(self::DEFAULT_PAGINATION_LIMIT_PARAM);
        $order = ['id' => 'DESC'];

        if (!($user = $this->getUser())) {
            return $this->createResponse(null, ['Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        $lessonRepository = $this->entityManager->getRepository(Lesson::class);

        $lessons = $lessonRepository->findPaginatedLessons(['user' => $user], $order, $limit, $page);
        $totalItems = $lessonRepository->countLessonsByCriteria(['user' => $user]);
        $totalPages = ceil($totalItems / $limit);

        return $this->createResponse(
            ['lessons' => $lessons, 'page' => $page, 'totalItems' => $totalItems, 'totalPages' => $totalPages], [],
            Response::HTTP_OK,
            ['groups' => self::LESSON_READ_GROUP]
        );
    }

    #[Route('/lesson', name: 'add_lesson', methods: ['POST'])]
    public function addLesson(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = $request->request->all();

        try {
            $lesson = $this->denormalizer->denormalize($data, Lesson::class);

            if (!($user = $this->getUser())) {
                return $this->createResponse(null, ['Authentication required.'], Response::HTTP_UNAUTHORIZED);
            }
            $lesson->setUser($user);

            $errors = $validator->validate($lesson);

            if ($errors->count() > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }

                return $this->createResponse(['errors' => $errorMessages],
                                             ['Validation failed'],
                                             Response::HTTP_BAD_REQUEST);
            }

            $this->lessonServices->addLesson($lesson);

            $this->logger->info('Lesson created successfully', ['lesson' => $lesson]);
            return $this->createResponse(['lesson' => $lesson],
                                         ['Lesson created successfully'],
                                         Response::HTTP_CREATED,
                                         ['groups' => self::LESSON_READ_GROUP]);
        } catch (ExceptionInterface|InvalidArgumentException|\Exception $e) {
            $this->logger->error('Lesson not created: ' . $e->getMessage());
            return $this->createResponse(null, ['Lesson not created', $e], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/lesson/{id}', name: 'remove_lesson', methods: ['DELETE'])]
    public function removeLesson(Lesson $lesson): JsonResponse
    {
        if (!$this->isGranted('LESSON_DELETE', $lesson)) {
            return $this->createResponse(
                null,
                ['You are not authorized to delete this lesson.'],
                Response::HTTP_FORBIDDEN
            );
        }

        try {
            $this->lessonServices->removeLesson($lesson);

            $this->logger->info('Lesson removed', ['lesson' => $lesson]);
            return $this->createResponse(null, ['Lesson remove successfully'], Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            $this->logger->error('Lesson remove error: ' . $e->getMessage());
            return $this->createResponse(
                null,
                ['Lesson remove error: ' . $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}