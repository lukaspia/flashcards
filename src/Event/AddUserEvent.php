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
     * @var \App\Entity\User
     */
    protected User $user;

    /**
     * @param \App\Entity\User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return \App\Entity\User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}