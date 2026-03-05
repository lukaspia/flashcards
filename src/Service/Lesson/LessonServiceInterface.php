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

    /**
     * Get paginated lessons for a specific user
     * 
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
    public function getUserLessonsWithPagination(\App\Entity\User $user, int $page, int $limit): array;
}