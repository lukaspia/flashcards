<?php

namespace App\Service\Lesson;

use App\Entity\Lesson;

interface LessonServiceInterface
{
    /**
     * @param \App\Entity\Lesson $lesson
     * @return \App\Entity\Lesson
     */
    public function addLesson(Lesson $lesson): Lesson;

    /**
     * @param \App\Entity\Lesson $lesson
     * @return \App\Entity\Lesson
     */
    public function updateLesson(Lesson $lesson): Lesson;

    /**
     * @param \App\Entity\Lesson $lesson
     * @return \App\Entity\Lesson
     */
    public function removeLesson(Lesson $lesson): Lesson;
}