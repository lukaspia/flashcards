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
     * @var \App\Entity\User
     */
    protected Lesson $lesson;

    /**
     * @param \App\Entity\User $user
     */
    public function __construct(Lesson $lesson)
    {
        $this->lesson = $lesson;
    }

    /**
     * @return \App\Entity\User
     */
    public function getLesson(): Lesson
    {
        return $this->lesson;
    }
}