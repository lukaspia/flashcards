<?php

declare(strict_types=1);


namespace App\DTO;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

readonly class UploadImageDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Word ID is required')]
        #[Assert\Positive(message: 'Word ID must be a valid positive integer')]
        public mixed $wordId,

        #[Assert\NotBlank(message: 'Please upload an image')]
        #[Assert\File(
            maxSize: '1024k',
            mimeTypes: ['image/jpeg', 'image/png', 'image/gif'],
            mimeTypesMessage: 'Please upload a valid image (JPEG, PNG, GIF)'
        )]
        public ?UploadedFile $image
    ) {
    }
}