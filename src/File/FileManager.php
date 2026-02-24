<?php

declare(strict_types=1);


namespace App\File;

use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;


readonly class FileManager implements FileManagerInterface
{
    private const DEFAULT_CHMOD = 0775;

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
        $this->createDirIfNotExists($targetDirectory);

        try {
            $file->move($targetDirectory, $fileName);
        } catch (FileException $e) {
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
            $this->createDirIfNotExists(dirname($destinationPath));

            $this->filesystem->rename($sourcePath, $destinationPath, true);
            return true;
        } catch (IOExceptionInterface $e) {
            throw new \RuntimeException("Error moving file: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param string $filePath
     * @return bool
     */
    public function removeFile(string $filePath): bool
    {
        try {
            $this->filesystem->remove($filePath);
            return true;
        } catch (IOExceptionInterface $e) {
            throw new \RuntimeException("Error removing file: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param string $directory
     * @param int $hoursThreshold
     * @return int
     */
    public function cleanupOldFiles(string $directory, int $hoursThreshold): int
    {
        if (!is_dir($directory)) {
            return 0;
        }

        $finder = new Finder();
        $finder->files()->in($directory)->date('before ' . $hoursThreshold . ' hours ago');

        $count = 0;
        foreach ($finder as $file) {
            $this->filesystem->remove($file->getRealPath());
            $count++;
        }

        return $count;
    }

    /**
     * @param string $directory
     * @return void
     */
    private function createDirIfNotExists(string $directory): void
    {
        if (!$this->filesystem->exists($directory)) {
            try {
                $this->filesystem->mkdir($directory, self::DEFAULT_CHMOD);
            } catch (IOExceptionInterface $e) {
                throw new \RuntimeException(sprintf('Directory "%s" could not be created', $directory), 0, $e);
            }
        }
    }
}