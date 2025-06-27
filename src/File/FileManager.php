<?php

declare(strict_types=1);


namespace App\File;

use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;


readonly class FileManager implements FileManagerInterface
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    /**
     * @param string $sourcePath
     * @param string $destinationPath
     * @return bool
     */
    public function moveFile(string $sourcePath, string $destinationPath): bool
    {
        if (!$this->filesystem->exists($sourcePath)) {
            return false;
        }

        try {
            if (!$this->filesystem->exists(dirname($destinationPath))) {
                $this->filesystem->mkdir(dirname($destinationPath));
            }

            $this->filesystem->rename($sourcePath, $destinationPath, true);
            return true;
        } catch (IOExceptionInterface $exception) {
            throw new \RuntimeException("Error moving file: " . $exception->getMessage());
        }
    }
}