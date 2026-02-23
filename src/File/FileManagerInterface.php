<?php

declare(strict_types=1);

namespace App\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileManagerInterface
{
    public function upload(UploadedFile $file, string $targetDirectory, string $fileName): void;

    /**
     * @param string $sourcePath
     * @param string $destinationPath
     * @return bool
     */
    public function moveFile(string $sourcePath, string $destinationPath): bool;

    /**
     * @param string $filePath
     * @return bool
     */
    public function removeFile(string $filePath): bool;
}