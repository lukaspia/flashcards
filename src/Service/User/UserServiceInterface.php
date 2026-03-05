<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;

interface UserServiceInterface
{
    /**
     * @param string $username
     * @param string $password
     * @param bool $isAdmin
     * @return \App\DTO\OperationResponseDTO
     */
    public function addUser(
        string $username,
        string $password,
        bool $isAdmin = false
    ): User;

    /**
     * @param string $username
     * @return \App\DTO\OperationResponseDTO
     */
    public function deleteUser(string $username): void;
}