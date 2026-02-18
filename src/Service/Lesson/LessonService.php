<?php

declare(strict_types=1);


namespace App\Service\Lesson;


use App\Entity\Lesson;
use App\Event\AddLessonEvent;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly class LessonService implements LessonServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
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
        $lesson = $this->saveLesson($lesson);
        $this->eventDispatcher->dispatch(new AddLessonEvent($lesson), AddLessonEvent::NAME);

        return $lesson;
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @return \App\Entity\Lesson
     */
    public function updateLesson(Lesson $lesson): Lesson
    {
        return $this->saveLesson($lesson);
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @return \App\Entity\Lesson
     */
    public function removeLesson(Lesson $lesson): Lesson
    {
        $this->entityManager->remove($lesson);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new AddLessonEvent($lesson), AddLessonEvent::NAME);

        return $lesson;
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @return \App\Entity\Lesson
     */
    private function saveLesson(Lesson $lesson): Lesson
    {
        $errors = $this->validator->validate($lesson);

        if (count($errors) > 0) {
            throw new InvalidArgumentException((string)$errors);
        }

        $this->entityManager->persist($lesson);
        $this->entityManager->flush();

        $this->wordImageServices->moveWordsImagesFromTemporary($lesson->getWords());

        return $lesson;
    }

    /**
     * @param \App\Entity\User $user
     * @param int $page
     * @param int $limit
     * @return array{
     *   lessons: \App\Entity\Lesson[],
     *   page: int,
     *   totalItems: int,
     *   totalPages: int
     * }
     */
    public function getUserLessonsWithPagination(User $user, int $page, int $limit): array
    {
        /** @var \App\Repository\LessonRepository $lessonRepository */
        $lessonRepository = $this->entityManager->getRepository(Lesson::class);
        $criteria = ['user' => $user];
        $order = ['id' => 'DESC'];

        $lessons = $lessonRepository->findPaginatedLessons($criteria, $order, $limit, $page);
        $totalItems = $lessonRepository->countLessonsByCriteria($criteria);
        $totalPages = ceil($totalItems / $limit);

        $page = min($page, $totalPages);

        return [
            'lessons' => $lessons,
            'page' => $page,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages
        ];
    }
}