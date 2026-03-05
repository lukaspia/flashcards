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
        return $subject instanceof Lesson && in_array($attribute, [self::EDIT, self::VIEW, self::DELETE], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return $subject->getUser() === $user;
    }
}
