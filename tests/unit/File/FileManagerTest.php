<?php

namespace App\Tests\File;

use App\File\FileManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManagerTest extends TestCase
{
    private MockObject|Filesystem $filesystem;
    private FileManager $fileManager;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->fileManager = new FileManager($this->filesystem);
    }

    public function testUploadMovesFileAndCreatesDirectory(): void
    {
        $targetDir = '/path/to/target';
        $fileName = 'image.jpg';

        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), 'test'), 'image.jpg', null, null, true])
            ->getMock();

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with($targetDir)
            ->willReturn(false);

        $this->filesystem->expects($this->once())
            ->method('mkdir')
            ->with($targetDir, 0775);

        $uploadedFile->expects($this->once())
            ->method('move')
            ->with($targetDir, $fileName);

        $this->fileManager->upload($uploadedFile, $targetDir, $fileName);
    }

    public function testMoveFileReturnsFalseIfSourceNotExists(): void
    {
        $this->filesystem->method('exists')->willReturn(false);

        $result = $this->fileManager->moveFile('old.txt', 'new.txt');

        $this->assertFalse($result);
    }

    public function testMoveFileSuccess(): void
    {
        $source = 'source.txt';
        $destDir = 'dir';
        $destFile = $destDir . '/dest.txt';

        $this->filesystem->method('exists')
            ->willReturnMap([
                                [$source, true],
                                [$destDir, false],
                            ]);

        $this->filesystem->expects($this->once())
            ->method('mkdir')
            ->with($destDir, 0775);

        $this->filesystem->expects($this->once())
            ->method('rename')
            ->with($source, $destFile, true);

        $result = $this->fileManager->moveFile($source, $destFile);

        $this->assertTrue($result);
    }

    public function testRemoveFileReturnsTrueOnSuccess(): void
    {
        $path = 'file.txt';

        $this->filesystem->expects($this->once())
            ->method('remove')
            ->with($path);

        $result = $this->fileManager->removeFile($path);
        $this->assertTrue($result);
    }

    public function testRemoveFileThrowsRuntimeExceptionOnFailure(): void
    {
        $this->filesystem->method('remove')
            ->willThrowException(new IOException('Access denied'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error removing file: Access denied');

        $this->fileManager->removeFile('protected.txt');
    }
}
