<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateLessonDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'ID is required')]
        #[Assert\Positive(message: 'ID must be a positive number')]
        public int $id,

        #[Assert\NotBlank(message: 'Name is required')]
        #[Assert\Length(
            min: 1,
            max: 255,
            minMessage: 'Name must be at least {{ limit }} characters long',
            maxMessage: 'Name cannot be longer than {{ limit }} characters'
        )]
        public string $name,

        #[Assert\NotBlank(message: 'Source language is required')]
        #[Assert\Regex(pattern: '/^[a-z]{2}-[A-Z]{2}$/')]
        public string $sourceLanguage,

        #[Assert\NotBlank(message: 'Target language is required')]
        #[Assert\Regex(pattern: '/^[a-z]{2}-[A-Z]{2}$/')]
        public string $targetLanguage,

        public ?array $words = null,
    ) {
    }
}
