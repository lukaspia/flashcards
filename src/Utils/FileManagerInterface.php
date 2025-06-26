<?php

declare(strict_types=1);

namespace App\Utils;

interface FileManagerInterface
{
    /**
     * @param string $sourcePath
     * @param string $destinationPath
     * @return bool
     */
    public function moveFile(string $sourcePath, string $destinationPath): bool;
}