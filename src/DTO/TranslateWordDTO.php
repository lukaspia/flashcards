<?php

declare(strict_types=1);


namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class TranslateWordDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Word is required')]
        public string $word,

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