<?php

declare(strict_types=1);

namespace App\Tests\Unit\File;

use App\File\FileManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class FileManagerTest extends TestCase
{
    private MockObject|Filesystem $filesystem;
    private FileManager $fileManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = $this->createMock(Filesystem::class);
        $this->fileManager = new FileManager($this->filesystem);
    }

    public function testMoveFileReturnsFalseWhenSourceFileDoesNotExist(): void
    {
        $sourcePath = 'non_existent_file.txt';
        $destinationPath = 'destination/file.txt';

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with($sourcePath)
            ->willReturn(false);

        $this->assertFalse($this->fileManager->moveFile($sourcePath, $destinationPath));
    }

    public function testMoveFileMovesFileSuccessfully(): void
    {
        // Use a real filesystem for this integration-style test to handle is_writable()
        $realFilesystem = new Filesystem();
        $fileManager = new FileManager($realFilesystem);

        $baseTmpDir = sys_get_temp_dir() . '/file_manager_test_success';
        $sourceDir = $baseTmpDir . '/source';
        $destinationDir = $baseTmpDir . '/destination';
        $sourcePath = $sourceDir . '/file.txt';
        $destinationPath = $destinationDir . '/file.txt';

        // Cleanup before test
        $realFilesystem->remove([$baseTmpDir]);

        // Setup test conditions
        $realFilesystem->mkdir([$sourceDir, $destinationDir]);
        $realFilesystem->touch($sourcePath);

        $this->assertTrue($fileManager->moveFile($sourcePath, $destinationPath));
        $this->assertFileExists($destinationPath);
        $this->assertFileDoesNotExist($sourcePath);

        // Cleanup after test
        $realFilesystem->remove([$baseTmpDir]);
    }

    public function testMoveFileCreatesDestinationDirectoryIfNeeded(): void
    {
        // Use a real filesystem for this integration-style test to handle is_writable()
        $realFilesystem = new Filesystem();
        $fileManager = new FileManager($realFilesystem);

        $baseTmpDir = sys_get_temp_dir() . '/file_manager_test';
        $sourceDir = $baseTmpDir . '/source';
        $destinationDir = $baseTmpDir . '/destination';
        $sourcePath = $sourceDir . '/file.txt';
        $destinationPath = $destinationDir . '/file.txt';

        // Cleanup before test
        $realFilesystem->remove([$baseTmpDir]);

        // Setup test conditions
        $realFilesystem->mkdir($sourceDir);
        $realFilesystem->touch($sourcePath);

        $this->assertTrue($fileManager->moveFile($sourcePath, $destinationPath));
        $this->assertFileExists($destinationPath);
        $this->assertFileDoesNotExist($sourcePath);

        // Cleanup after test
        $realFilesystem->remove([$baseTmpDir]);
    }

    public function testMoveFileThrowsExceptionOnFilesystemError(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error moving file: An error occurred while moving file.');

        $sourcePath = 'source/file.txt';
        $destinationPath = 'destination/file.txt';
        $destinationDir = 'destination';

        $this->filesystem->method('exists')
            ->willReturnMap([
                [$sourcePath, true],
                [$destinationDir, true],
            ]);

        $this->filesystem->expects($this->once())
            ->method('rename')
            ->with($sourcePath, $destinationPath, true)
            ->willThrowException(new IOException('An error occurred while moving file.'));

        $this->fileManager->moveFile($sourcePath, $destinationPath);
    }

    public function testRemoveFileRemovesExistingFile(): void
    {
        $filePath = 'path/to/file.txt';

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with($filePath)
            ->willReturn(true);

        $this->filesystem->expects($this->once())
            ->method('remove')
            ->with($filePath);

        $this->assertTrue($this->fileManager->removeFile($filePath));
    }

    public function testRemoveFileReturnsTrueForNonExistingFile(): void
    {
        $filePath = 'path/to/non_existent_file.txt';

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with($filePath)
            ->willReturn(false);

        $this->filesystem->expects($this->never())
            ->method('remove');

        $this->assertTrue($this->fileManager->removeFile($filePath));
    }

    public function testRemoveFileThrowsExceptionOnFilesystemError(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error removing file: An error occurred while removing file.');

        $filePath = 'path/to/file.txt';

        $this->filesystem->method('exists')
            ->with($filePath)
            ->willReturn(true);

        $this->filesystem->expects($this->once())
            ->method('remove')
            ->with($filePath)
            ->willThrowException(new IOException('An error occurred while removing file.'));

        $this->fileManager->removeFile($filePath);
    }
}
