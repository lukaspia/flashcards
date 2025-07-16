<?php

namespace App\Factory;

use App\Entity\Lesson;
use Symfony\Component\Security\Core\User\UserInterface;

interface LessonFactoryInterface
{
    public function createFromRequestData(array $data, UserInterface $user): Lesson;

    public function updateFromRequestData(Lesson $lesson, array $data): Lesson;
}