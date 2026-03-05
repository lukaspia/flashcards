<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class AddLessonDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Name is required')]
        #[Assert\Length(
            min: 1,
            max: 255,
            minMessage: 'Name must be at least {{ limit }} characters long',
            maxMessage: 'Name cannot be longer than {{ limit }} characters'
        )]
        public string $name,

        #[Assert\NotBlank(message: 'Source language is required')]
        #[Assert\Regex(
            pattern: '/^[a-z]{2}-[A-Z]{2}$/',
            message: 'Language must be in format xx-XX (e.g. pl-PL)'
        )]
        public string $sourceLanguage = 'pl-PL',

        #[Assert\NotBlank(message: 'Target language is required')]
        #[Assert\Regex(
            pattern: '/^[a-z]{2}-[A-Z]{2}$/',
            message: 'Language must be in format xx-XX (e.g. en-US)'
        )]
        public string $targetLanguage = 'en-US',
    ) {
    }
}
