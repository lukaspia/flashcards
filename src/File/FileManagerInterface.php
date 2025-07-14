<?php

declare(strict_types=1);

namespace App\File;

interface FileManagerInterface
{
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