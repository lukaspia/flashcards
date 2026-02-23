<?php

declare(strict_types=1);


namespace App\File;

use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;


readonly class FileManager implements FileManagerInterface
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string $targetDirectory
     * @param string $fileName
     * @return void
     */
    public function upload(UploadedFile $file, string $targetDirectory, string $fileName): void
    {
        try {
            if (!$this->filesystem->exists($targetDirectory)) {
                $this->filesystem->mkdir($targetDirectory, 0775);
            }

            $file->move($targetDirectory, $fileName);
        } catch (FileException | IOExceptionInterface $e) {
            throw new \RuntimeException(sprintf('Failed to upload file: %s', $e->getMessage()), 0, $e);
        }
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