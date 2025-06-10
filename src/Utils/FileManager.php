<?php

declare(strict_types=1);


namespace App\Utils;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;


class FileManager
{
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function moveFile(string $sourcePath, string $destinationPath): bool
    {
        if(!$this->filesystem->exists($sourcePath)) {
            return false;
        }

        try {
            if(!$this->filesystem->exists(dirname($destinationPath))) {
                $this->filesystem->mkdir(dirname($destinationPath));
            }

            $this->filesystem->rename($sourcePath, $destinationPath, true);
            return true;
        } catch (IOExceptionInterface $exception) {
            throw new \RuntimeException("Error moving file: " . $exception->getMessage());
        }
    }
}