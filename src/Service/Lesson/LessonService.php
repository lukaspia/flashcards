<?php

declare(strict_types=1);


namespace App\Service\Lesson;


use App\Entity\Lesson;
use App\Event\AddLessonEvent;
use App\Entity\User;
use App\Event\RemoveLessonEvent;
use App\Repository\LessonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly class LessonService implements LessonServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LessonRepository $lessonRepository,
        private ValidatorInterface $validator,
        private EventDispatcherInterface $eventDispatcher,
        private WordImageServiceInterface $wordImageServices
    ) {
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @return \App\Entity\Lesson
     */
    public function addLesson(Lesson $lesson): Lesson
    {
        $this->validate($lesson);

        $this->entityManager->wrapInTransaction(function () use ($lesson) {
            $this->entityManager->persist($lesson);
            $this->entityManager->flush();

            $this->wordImageServices->moveWordsImagesFromTemporary($lesson->getWords());
        });

        $this->eventDispatcher->dispatch(new AddLessonEvent($lesson), AddLessonEvent::NAME);

        return $lesson;
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @return \App\Entity\Lesson
     */
    public function updateLesson(Lesson $lesson): Lesson
    {
        $this->validate($lesson);

        $this->entityManager->wrapInTransaction(function () use ($lesson) {
            $this->entityManager->flush();
            $this->wordImageServices->moveWordsImagesFromTemporary($lesson->getWords());
        });

        return $lesson;
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @return \App\Entity\Lesson
     */
    public function removeLesson(Lesson $lesson): Lesson
    {
        $this->entityManager->wrapInTransaction(function () use ($lesson) {
            $this->entityManager->remove($lesson);
            $this->entityManager->flush();
        });

        $this->eventDispatcher->dispatch(new RemoveLessonEvent($lesson), RemoveLessonEvent::NAME);

        return $lesson;
    }

    /**
     * @return array{lessons: Lesson[], page: int, totalItems: int, totalPages: int}
     */
    public function getUserLessonsWithPagination(User $user, int $page, int $limit): array
    {
        $paginator = $this->lessonRepository->getPaginatedLessons(
            ['user' => $user],
            ['id' => 'DESC'],
            $limit,
            $page
        );

        $totalItems = count($paginator);
        $totalPages = (int)ceil($totalItems / $limit);
        $safePage = $totalPages > 0 ? max(1, min($page, $totalPages)) : 1;

        return [
            'lessons' => iterator_to_array($paginator),
            'page' => $safePage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages
        ];
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @return void
     */
    private function validate(Lesson $lesson): void
    {
        $errors = $this->validator->validate($lesson);

        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string)$errors);
        }
    }
}