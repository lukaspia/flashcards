<?php

declare(strict_types=1);


namespace App\Event;


use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 *
 */
class AddUserEvent extends Event
{
    /**
     *
     */
    public const NAME = 'user.added';

    /**
     * @param \App\Entity\User $user
     */
    public function __construct(protected User $user)
    {
    }

    /**
     * @return \App\Entity\User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}