<?php

declare(strict_types=1);


namespace App\Controller\Api;


use App\Entity\Lesson;
use App\Entity\Word;
use App\Service\AI\AIGeneratorInterface;
use App\Service\Lesson\LessonServices;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LessonController extends AbstractApiController
{
    private const LESSON_READ_GROUP = 'lesson:read';
    private const LESSON_WRITE_GROUP = 'lesson:write';
    private const DEFAULT_PAGINATION_LIMIT_PARAM = 'pagination_default_limit';

    public function __construct(
        EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private LessonServices $lessonServices,
        private LoggerInterface $logger,
        private ValidatorInterface $validator
    ) {
        parent::__construct($entityManager);
    }

    #[Route('/lessons', name: 'lessons', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        if (!($user = $this->getUser())) {
            return $this->createResponse(null, ['Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        $page = $request->query->getInt('page', 1);
        $limit = $this->getParameter(self::DEFAULT_PAGINATION_LIMIT_PARAM);
        $criteria = ['user' => $user];
        $order = ['id' => 'DESC'];

        /** @var \App\Repository\LessonRepository $lessonRepository */
        $lessonRepository = $this->entityManager->getRepository(Lesson::class);

        try {
            $lessons = $lessonRepository->findPaginatedLessons($criteria, $order, $limit, $page);
            $totalItems = $lessonRepository->countLessonsByCriteria($criteria);
            $totalPages = ceil($totalItems / $limit);

            return $this->createResponse(
                ['lessons' => $lessons, 'page' => $page, 'totalItems' => $totalItems, 'totalPages' => $totalPages], [],
                Response::HTTP_OK,
                ['groups' => self::LESSON_READ_GROUP]
            );
        } catch (\Exception $e) {
            $this->logger->error('Error fetching lessons: ' . $e->getMessage(), ['exception' => $e]);
            return $this->createResponse(
                null,
                ['An error occurred while fetching lessons.'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/lesson/{id}', name: 'get_lesson', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getLesson(Lesson $lesson): JsonResponse
    {
        if (!($this->getUser())) {
            return $this->createResponse(null, ['Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        if(!$this->isGranted('LESSON_VIEW', $lesson)) {
            return $this->createResponse(null, ['You are not authorized to view this lesson.'], Response::HTTP_FORBIDDEN);
        }

        return $this->createResponse(
            ['lesson' => $lesson], [],
            Response::HTTP_OK,
            ['groups' => self::LESSON_READ_GROUP]
        );
    }

    #[Route('/lesson', name: 'add_lesson', methods: ['POST'])]
    public function addLesson(Request $request): JsonResponse
    {
        $data = $request->request->all();

        try {
            $lesson = $this->denormalizer->denormalize($data, Lesson::class);

            if (!($user = $this->getUser())) {
                return $this->createResponse(null, ['Authentication required.'], Response::HTTP_UNAUTHORIZED);
            }
            $lesson->setUser($user);

            $errors = $this->validator->validate($lesson);

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

    #[Route('/lesson', name: 'update_lesson', methods: ['PUT'])]
    public function updateLesson(Request $request): JsonResponse
    {
        $data = $request->toArray();

        $existingLesson = null;
        if (isset($data['id'])) {
            $existingLesson = $this->entityManager->getRepository(Lesson::class)->find($data['id']);
        }

        if (!$existingLesson) {
            return $this->createResponse(null, ['Lesson not found'], Response::HTTP_NOT_FOUND);
        }

        if(!$this->isGranted('LESSON_EDIT', $existingLesson)) {
            return $this->createResponse(null, ['You are not authorized to edit this lesson.'], Response::HTTP_FORBIDDEN);
        }

        $words = $existingLesson->getWords();
        $words->clear();

        if (isset($data['words'])) {
            $lessonWords = $this->entityManager->getRepository(Word::class)->findByLessonId($data['id']);
            foreach ($data['words'] as $word) {
                $context = [];
                if (isset($word['id'], $lessonWords[$word['id']])) {
                    $context = [AbstractNormalizer::OBJECT_TO_POPULATE => $lessonWords[$word['id']]];
                }

                try {
                    $wordEntity = $this->serializer->denormalize($word, Word::class, null, $context);
                } catch (ExceptionInterface $e) {
                    return $this->createResponse(
                        null,
                        ['Invalid data: ' . $e->getMessage()],
                        Response::HTTP_BAD_REQUEST
                    );
                }

                $wordEntity->setLesson($existingLesson);
                $words->add($wordEntity);
            }
        }

        try {
            $lesson = $this->serializer->denormalize($data, Lesson::class, null, [
                AbstractNormalizer::OBJECT_TO_POPULATE => $existingLesson,
                AbstractNormalizer::GROUPS => [self::LESSON_WRITE_GROUP],
            ]);
        } catch (ExceptionInterface $e) {
            return $this->createResponse(null, ['Invalid data: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $this->lessonServices->updateLesson($lesson);

        return $this->createResponse(
            ['lesson' => $lesson], ['Lesson updated successfully'],
            Response::HTTP_OK,
            ['groups' => self::LESSON_READ_GROUP]
        );
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

    #[Route('/lesson/message', name: 'get_lesson_message', methods: ['GET'])]
    public function getSuccessMessage(AIGeneratorInterface $geminiService): JsonResponse
    {
        try {
            $message = $geminiService->generateText('Wygeneruj krótki tekst, który pochwali osobę której dobrze poszła nauka słówek języka obcego.');
        } catch (\Exception $e) {
            $this->logger->error('Lesson success message error: ' . $e->getMessage());
            return $this->createResponse(
                null,
                ['Lesson success message error: ' . $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->createResponse(['message' => $message], ['Lesson success message generated successfully'], Response::HTTP_OK);
    }
}