<?php

declare(strict_types=1);


namespace App\Security\Voter;

use App\Entity\Word;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 *
 */
final class WordVoter extends Voter
{
    /**
     *
     */
    public const DELETE_IMAGE = 'DELETE_IMAGE';

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::DELETE_IMAGE])
            && $subject instanceof Word;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Word $word */
        $word = $subject;
        $lesson = $word->getLesson();

        if (!$lesson) {
            return false;
        }

        return $lesson->getUser() === $user;
    }
}