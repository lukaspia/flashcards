<?php

namespace App\Factory;

use App\DTO\AddLessonDTO;
use App\DTO\UpdateLessonDTO;
use App\Entity\Lesson;
use Symfony\Component\Security\Core\User\UserInterface;

interface LessonFactoryInterface
{
    public function createFromDTO(AddLessonDTO $dto, UserInterface $user): Lesson;

    public function updateFromDTO(Lesson $lesson, UpdateLessonDTO $dto): Lesson;
}