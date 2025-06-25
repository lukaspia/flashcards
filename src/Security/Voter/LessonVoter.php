<?php

namespace App\Security\Voter;

use App\Entity\Lesson;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class LessonVoter extends Voter
{
    public const DELETE = 'LESSON_DELETE';
    public const EDIT = 'LESSON_EDIT';
    public const VIEW = 'LESSON_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Lesson;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canDelete(Lesson $lesson, UserInterface $user): bool
    {
        return $this->isLessonOwner($lesson, $user);
    }

    private function canView(Lesson $lesson, UserInterface $user): bool
    {
        return $this->isLessonOwner($lesson, $user);
    }

    private function canEdit(Lesson $lesson, UserInterface $user): bool
    {
        return $this->isLessonOwner($lesson, $user);
    }

    private function isLessonOwner(Lesson $lesson, UserInterface $user): bool
    {
        return $lesson->getUser() === $user;
    }
}
