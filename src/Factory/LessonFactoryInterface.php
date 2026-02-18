<?php

namespace App\Factory;

use App\Entity\Lesson;
use App\DTO\AddLessonDTO;
use Symfony\Component\Security\Core\User\UserInterface;

interface LessonFactoryInterface
{
    public function createFromDTO(AddLessonDTO $dto, UserInterface $user): Lesson;

    public function updateFromRequestData(Lesson $lesson, array $data): Lesson;
}