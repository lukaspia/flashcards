<?php

declare(strict_types=1);


namespace App\Service\Lesson;


use App\Entity\Lesson;
use App\Event\AddLessonEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LessonServices
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    private ValidatorInterface $validator;
    /**
     * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
     */
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @return \App\Entity\Lesson
     */
    public function addLesson(Lesson $lesson): Lesson
    {
        $errors = $this->validator->validate($lesson);

        if (count($errors) > 0) {
            throw new InvalidArgumentException((string)$errors);
        }

        $this->entityManager->persist($lesson);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new AddLessonEvent($lesson), AddLessonEvent::NAME);

        return $lesson;
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
}