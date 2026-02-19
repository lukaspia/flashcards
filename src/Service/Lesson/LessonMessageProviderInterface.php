<?php

namespace App\Service\Lesson;

/**
 *
 */
interface LessonMessageProviderInterface
{
    /**
     * @return string
     */
    public function getCongratsMessage(): string;
}