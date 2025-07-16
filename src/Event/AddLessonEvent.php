<?php

declare(strict_types=1);


namespace App\Event;


use App\Entity\Lesson;
use Symfony\Contracts\EventDispatcher\Event;

/**
 *
 */
class AddLessonEvent extends Event
{
    /**
     *
     */
    public const NAME = 'lesson.added';

    /**
     * @param \App\Entity\Lesson $lesson
     */
    public function __construct(protected readonly Lesson $lesson)
    {
    }

    /**
     * @return \App\Entity\Lesson
     */
    public function getLesson(): Lesson
    {
        return $this->lesson;
    }
}