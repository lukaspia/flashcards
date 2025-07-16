<?php

declare(strict_types=1);


namespace App\File;


use App\File\FileNameGeneratorInterface;

class FileNameGenerator implements FileNameGeneratorInterface
{

    /**
     * @param string $identifier
     * @param string $originalFilename
     * @return string
     */
    public function generate(string $identifier, string $originalFilename): string
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        return md5($identifier) . ($extension ? '.' . $extension : '');
    }
}