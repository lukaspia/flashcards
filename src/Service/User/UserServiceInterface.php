<?php

declare(strict_types=1);

namespace App\Service\User;

use App\DTO\OperationResponse;

interface UserServiceInterface
{
    /**
     * @param string $username
     * @param string $password
     * @param bool $isAdmin
     * @return \App\DTO\OperationResponse
     */
    public function addUser(
        string $username,
        string $password,
        bool $isAdmin = false
    ): OperationResponse;

    /**
     * @param string $username
     * @return \App\DTO\OperationResponse
     */
    public function deleteUser(string $username): OperationResponse;
}