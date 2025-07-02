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
            $destinationDir = dirname($destinationPath);
            if (!$this->filesystem->exists($destinationDir)) {
                $this->filesystem->mkdir($destinationDir);
            }

            if (!is_writable($destinationDir)) {
                throw new \RuntimeException(sprintf('Destination directory "%s" is not writable', $destinationDir));
            }

            $this->filesystem->rename($sourcePath, $destinationPath, true);
            return true;
        } catch (IOExceptionInterface $exception) {
            throw new \RuntimeException("Error moving file: " . $exception->getMessage());
        }
    }

    /**
     * @param string $filePath
     * @return bool
     */
    public function removeFile(string $filePath): bool
    {
        try {
            if ($this->filesystem->exists($filePath)) {
                $this->filesystem->remove($filePath);
                return true;
            }
            return true;
        } catch (IOExceptionInterface $exception) {
            throw new \RuntimeException("Error removing file: " . $exception->getMessage());
        }
    }
}