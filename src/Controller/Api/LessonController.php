<?php

declare(strict_types=1);


namespace App\Controller\Api;


use App\Entity\Lesson;
use App\Factory\LessonFactoryInterface;
use App\Service\AI\AIGeneratorInterface;
use App\Service\Lesson\LessonServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class LessonController extends AbstractApiController
{
    public function __construct(
        EntityManagerInterface $entityManager,
        private readonly LessonServiceInterface $lessonServices,
        private readonly LoggerInterface $logger,
        private readonly LessonFactoryInterface $lessonFactory,
    ) {
        parent::__construct($entityManager);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/lessons', name: 'lessons', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        if (!($user = $this->getUser())) {
            return $this->createResponse(null, ['Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        $page = $request->query->getInt('page', 1);
        $limit = $this->getParameter('pagination_default_limit');

        if ($page < 1) {
            $page = 1;
        }
        if ($limit < 1 || $limit > 100) {
            $limit = $this->getParameter('pagination_default_limit');
        }

        try {
            $paginationData = $this->lessonServices->getUserLessonsWithPagination($user, $page, $limit);

            return $this->createResponse(
                $paginationData,
                [],
                Response::HTTP_OK,
                ['groups' => Lesson::LESSON_READ_GROUP]
            );
        } catch (\Exception $e) {
            $this->logger->error('Error fetching lessons for user {userId}: {message}', [
                'userId' => $user->getId(),
                'message' => $e->getMessage(),
                'page' => $page,
                'limit' => $limit,
                'exception' => $e
            ]);
            return $this->createResponse(
                null,
                ['Unable to retrieve lessons. Please try again later.'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/lessons/{id}', name: 'get_lesson', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getLesson(Lesson $lesson): JsonResponse
    {
        if (!($this->getUser())) {
            $this->logger->warning('Unauthenticated user attempted to access lesson', [
                'lessonId' => $lesson->getId(),
                'route' => 'get_lesson'
            ]);
            return $this->createResponse(null, ['Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->isGranted('LESSON_VIEW', $lesson)) {
            $this->logger->warning('User attempted to access lesson without permission', [
                'userId' => $this->getUser()->getId(),
                'lessonId' => $lesson->getId(),
                'lessonOwnerId' => $lesson->getUser()->getId()
            ]);
            return $this->createResponse(
                null,
                ['You are not authorized to view this lesson.'],
                Response::HTTP_FORBIDDEN
            );
        }

        return $this->createResponse(
            ['lesson' => $lesson], [],
            Response::HTTP_OK,
            ['groups' => Lesson::LESSON_READ_GROUP]
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/lessons', name: 'add_lesson', methods: ['POST'])]
    public function addLesson(Request $request): JsonResponse
    {
        if (!($user = $this->getUser())) {
            $this->logger->warning('Unauthenticated user attempted to create lesson', [
                'route' => 'add_lesson'
            ]);
            return $this->createResponse(null, ['Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $data = $request->request->all();
            if (empty($data)) {
                return $this->createResponse(
                    null,
                    ['No data provided'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $requiredFields = ['name', 'sourceLanguage', 'targetLanguage'];
            $missingFields = array_diff($requiredFields, array_keys($data));

            if (!empty($missingFields)) {
                return $this->createResponse(
                    null,
                    ['Missing required fields: ' . implode(', ', $missingFields)],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $lesson = $this->lessonFactory->createFromRequestData($data, $user);
            $this->lessonServices->addLesson($lesson);

            $this->logger->info('Lesson created successfully', [
                'lessonId' => $lesson->getId(),
                'userId' => $user->getId(),
                'lessonName' => $lesson->getName()
            ]);
            return $this->createResponse(
                ['lesson' => $lesson],
                ['Lesson created successfully'],
                Response::HTTP_CREATED,
                ['groups' => Lesson::LESSON_READ_GROUP]
            );
        } catch (\RuntimeException|InvalidArgumentException|\Exception $e) {
            $this->logger->error('Failed to create lesson for user {userId}: {message}', [
                'userId' => $user->getId(),
                'message' => $e->getMessage(),
                'requestData' => $data,
                'exception' => $e
            ]);
            return $this->createResponse(
                null,
                ['Unable to create lesson. Please check your data and try again.'],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/lessons', name: 'update_lesson', methods: ['PUT'])]
    public function updateLesson(Request $request): JsonResponse
    {
        $data = $request->toArray();

        if (empty($data)) {
            return $this->createResponse(
                null,
                ['No data provided'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $existingLesson = null;
        if (isset($data['id'])) {
            $existingLesson = $this->entityManager->getRepository(Lesson::class)->find($data['id']);
        }

        if (!$existingLesson) {
            return $this->createResponse(null, ['Lesson not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->isGranted('LESSON_EDIT', $existingLesson)) {
            return $this->createResponse(
                null,
                ['You are not authorized to edit this lesson.'],
                Response::HTTP_FORBIDDEN
            );
        }

        try {
            $lesson = $this->lessonFactory->updateFromRequestData($existingLesson, $data);
            $this->lessonServices->updateLesson($lesson);

            return $this->createResponse(
                ['lesson' => $lesson], ['Lesson updated successfully'],
                Response::HTTP_OK,
                ['groups' => Lesson::LESSON_READ_GROUP]
            );
        } catch (\RuntimeException|InvalidArgumentException|\Exception $e) {
            return $this->createResponse(null, ['Invalid data: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/lessons/{id}', name: 'remove_lesson', methods: ['DELETE'])]
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

            $this->logger->info('Lesson removed successfully', ['lesson' => $lesson]);
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

    /**
     * @param \App\Service\AI\AIGeneratorInterface $aiGeneratorService
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/lessons/message', name: 'get_lesson_message', methods: ['GET'])]
    public function getSuccessMessage(AIGeneratorInterface $aiGeneratorService): JsonResponse
    {
        try {
            $prompt = 'Generate one, short, encouraging message just in Polish (don\'t give me translation in English), to congratulate someone on their successful foreign language vocabulary learning.';
            $message = $aiGeneratorService->generateText($prompt);

            return $this->createResponse(
                ['message' => $message],
                ['Lesson success message generated successfully'],
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            $this->logger->error('Lesson success message error: ' . $e->getMessage());
            return $this->createResponse(
                null,
                ['Lesson success message error: ' . $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}