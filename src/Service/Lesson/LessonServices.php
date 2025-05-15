<?php

declare(strict_types=1);


namespace App\Service\Lesson;


use App\Entity\Lesson;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @return \App\Entity\Lesson
     */
    public function addLesson(Lesson $lesson): Lesson
    {
        $errors = $this->validator->validate($lesson);

        if(count($errors) > 0) {
            throw new InvalidArgumentException((string) $errors);
        }

        $this->entityManager->persist($lesson);
        $this->entityManager->flush();

        return $lesson;
    }
}