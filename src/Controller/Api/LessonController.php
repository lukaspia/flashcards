<?php

declare(strict_types=1);


namespace App\Controller\Api;


use App\Controller\Traits\AuthenticationTrait;
use App\DTO\AddLessonDTO;
use App\DTO\UpdateLessonDTO;
use App\Entity\Lesson;
use App\Factory\LessonFactoryInterface;
use App\Service\AI\AIGeneratorInterface;
use App\Service\Lesson\LessonServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Annotation\Route;

class LessonController extends AbstractApiController
{
    use AuthenticationTrait;
    public function __construct(
        EntityManagerInterface $entityManager,
        private readonly LessonServiceInterface $lessonServices,
        protected readonly LoggerInterface $logger,
        private readonly LessonFactoryInterface $lessonFactory,
        private readonly ValidatorInterface $validator,
        private readonly SerializerInterface $serializer,
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
        $user = $this->requireAuthenticatedUser();

        $params = $this->getPaginationParams($request);

        $paginationData = $this->lessonServices->getUserLessonsWithPagination(
            $user,
            $params['page'],
            $params['limit']
        );

        return $this->createResponse(
            $paginationData,
            [],
            Response::HTTP_OK,
            ['groups' => Lesson::LESSON_READ_GROUP]
        );
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/lessons/{id}', name: 'get_lesson', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getLesson(Lesson $lesson): JsonResponse
    {
        $this->requireAuthenticatedUser();

        $this->denyAccessUnlessGranted('LESSON_VIEW', $lesson);

        return $this->createResponse(
            ['lesson' => $lesson],
            [],
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
        $user = $this->requireAuthenticatedUser();
        $data = $request->request->all();

        try {
            $dto = $this->serializer->denormalize($data, AddLessonDTO::class);

            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                return $this->createResponse(
                    null,
                    $this->formatErrors($errors),
                    Response::HTTP_BAD_REQUEST
                );
            }

            $lesson = $this->lessonFactory->createFromDTO($dto, $user);
            $this->lessonServices->addLesson($lesson);

            $this->logger->info('Lesson created successfully', [
                'lessonId' => $lesson->getId(),
                'userId' => $user->getId()
            ]);

            return $this->createResponse(
                ['lesson' => $lesson],
                ['Lesson created successfully'],
                Response::HTTP_CREATED,
                ['groups' => Lesson::LESSON_READ_GROUP]
            );

        } catch (SerializerException $e) {
            return $this->createResponse(
                null,
                ['Invalid data format provided.'],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to create lesson: ' . $e->getMessage(), [
                'userId' => $user->getId(),
                'requestData' => $data,
                'exception' => $e
            ]);

            return $this->createResponse(
                null,
                ['Unable to create lesson. Please check your data and try again.'],
                Response::HTTP_INTERNAL_SERVER_ERROR
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
        $user = $this->requireAuthenticatedUser();
        $data = $request->toArray();

        try {
            $dto = $this->serializer->denormalize($data, UpdateLessonDTO::class);

            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                return $this->createResponse(null, $this->formatErrors($errors), Response::HTTP_BAD_REQUEST);
            }

            $existingLesson = $this->entityManager->getRepository(Lesson::class)->find($dto->id);

            if (!$existingLesson) {
                return $this->createResponse(null, ['Lesson not found'], Response::HTTP_NOT_FOUND);
            }

            if (!$this->isGranted('LESSON_EDIT', $existingLesson)) {
                $this->logger->warning('Unauthorized edit attempt', [
                    'userId' => $user->getId(),
                    'lessonId' => $existingLesson->getId()
                ]);
                return $this->createResponse(null, ['You are not authorized to edit this lesson.'], Response::HTTP_FORBIDDEN);
            }

            $lesson = $this->lessonFactory->updateFromDTO($existingLesson, $dto);
            $this->lessonServices->updateLesson($lesson);

            $this->logger->info('Lesson updated successfully', [
                'lessonId' => $lesson->getId(),
                'userId' => $user->getId()
            ]);

            return $this->createResponse(
                ['lesson' => $lesson],
                ['Lesson updated successfully'],
                Response::HTTP_OK,
                ['groups' => Lesson::LESSON_READ_GROUP]
            );

        } catch (SerializerException $e) {
            return $this->createResponse(null, ['Invalid data format'], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error('Update failed: ' . $e->getMessage(), [
                'userId' => $user->getId(),
                'exception' => $e
            ]);

            return $this->createResponse(null, ['Unable to update lesson.'], Response::HTTP_INTERNAL_SERVER_ERROR);
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

    private function formatErrors($errors): array
    {
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = $error->getMessage();
        }
        return $messages;
    }
}