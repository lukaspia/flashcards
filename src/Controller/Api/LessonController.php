<?php

declare(strict_types=1);


namespace App\Controller\Api;


use App\Controller\Traits\AuthenticationTrait;
use App\DTO\AddLessonDTO;
use App\DTO\UpdateLessonDTO;
use App\Entity\Lesson;
use App\Entity\User;
use App\Factory\LessonFactoryInterface;
use App\Service\Lesson\LessonMessageProviderInterface;
use App\Service\Lesson\LessonServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;

class LessonController extends AbstractApiController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LessonServiceInterface $lessonServices,
        protected readonly LoggerInterface $logger,
        private readonly LessonFactoryInterface $lessonFactory,
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\User $user
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/lessons', name: 'lessons', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(Request $request, #[CurrentUser] User $user): JsonResponse
    {
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
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getLesson(Lesson $lesson): JsonResponse
    {
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
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function addLesson(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $dto = $this->serializer->denormalize($request->request->all(), AddLessonDTO::class);

        $this->validateDto($dto);

        $lesson = $this->lessonFactory->createFromDTO($dto, $user);
        $this->lessonServices->addLesson($lesson);

        return $this->createResponse(
            ['lesson' => $lesson],
            ['Lesson created successfully'],
            Response::HTTP_CREATED,
            ['groups' => Lesson::LESSON_READ_GROUP]
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/lessons', name: 'update_lesson', methods: ['PUT'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function updateLesson(Request $request): JsonResponse
    {
        /** @var UpdateLessonDTO $dto */
        $dto = $this->serializer->denormalize($request->toArray(), UpdateLessonDTO::class);
        $this->validateDto($dto);

        $existingLesson = $this->entityManager->getRepository(Lesson::class)->find($dto->id);

        if (!$existingLesson) {
            throw $this->createNotFoundException('Lesson not found');
        }

        $this->denyAccessUnlessGranted('LESSON_EDIT', $existingLesson);

        $lesson = $this->lessonFactory->updateFromDTO($existingLesson, $dto);
        $this->lessonServices->updateLesson($lesson);

        return $this->createResponse(
            ['lesson' => $lesson],
            ['Lesson updated successfully'],
            Response::HTTP_OK,
            ['groups' => Lesson::LESSON_READ_GROUP]
        );
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/lessons/{id}', name: 'remove_lesson', methods: ['DELETE'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function removeLesson(Lesson $lesson): JsonResponse
    {
        $this->denyAccessUnlessGranted('LESSON_DELETE', $lesson);

        $this->lessonServices->removeLesson($lesson);

        return $this->createResponse(null, ['Lesson removed successfully'], Response::HTTP_NO_CONTENT);
    }

    /**
     * @param \App\Service\AI\AIGeneratorInterface $aiGeneratorService
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route('/lessons/message', name: 'get_lesson_message', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getSuccessMessage(LessonMessageProviderInterface $messageProvider): JsonResponse
    {
        return $this->createResponse(
            ['message' => $messageProvider->getCongratsMessage()],
            ['Lesson success message generated successfully'],
            Response::HTTP_OK
        );
    }
}