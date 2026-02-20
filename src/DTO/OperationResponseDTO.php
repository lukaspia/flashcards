<?php

declare(strict_types=1);


namespace App\DTO;

/**
 *
 */
readonly class OperationResponseDTO
{
    public function __construct(
        public bool $success,
        public string $message
    ) {
    }
}