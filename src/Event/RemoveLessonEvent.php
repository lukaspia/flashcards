<?php

declare(strict_types=1);


namespace App\Event;


use App\Entity\Lesson;
use Symfony\Contracts\EventDispatcher\Event;

/**
 *
 */
class RemoveLessonEvent extends Event
{
    /**
     *
     */
    public const NAME = 'lesson.remove';

    /**
     * @param \App\Entity\Lesson $lesson
     */
    public function __construct(protected Lesson $lesson)
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