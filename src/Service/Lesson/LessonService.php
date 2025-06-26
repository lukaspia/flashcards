<?php

declare(strict_types=1);


namespace App\Service\Lesson;


use App\Entity\Lesson;
use App\Event\AddLessonEvent;
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
        private WordImageServiceInterface $wordServices
    ) {
    }
//TODO zamienić w każdej klasie wstrzyknięcia na php8 i jako readonly
//TODO zastanowic się co z fabrykami
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

        $this->wordServices->moveWordsImagesFromTemporary($lesson->getWords());

        return $lesson;
    }
}