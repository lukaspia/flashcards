<?php

namespace App\File;

interface FileNameGeneratorInterface
{
    public function generate(string $identifier, string $originalFilename): string;
}