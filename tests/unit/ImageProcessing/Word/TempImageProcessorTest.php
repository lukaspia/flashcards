<?php

namespace App\Tests\ImageProcessing\Word;

use App\ImageProcessing\Word\TempImageProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TempImageProcessorTest extends TestCase
{
    private string $tempDir;
    private string $relativeDir = 'uploads/images/word';
    private TempImageProcessor $processor;
    private UploadedFile|MockObject $uploadedFile;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/word_images_test';
        $this->processor = new TempImageProcessor($this->tempDir, $this->relativeDir);
        
        // Create a mock UploadedFile
        $this->uploadedFile = $this->createMock(UploadedFile::class);
        $this->uploadedFile->method('getClientOriginalName')->willReturn('test.jpg');
    }

    protected function tearDown(): void
    {
        // Clean up test directory
        if (is_dir($this->tempDir)) {
            array_map('unlink', glob("$this->tempDir/*"));
            rmdir($this->tempDir);
        }
    }

    public function testProcessSuccessfullyMovesFile(): void
    {
        // Arrange
        $filename = 'test_' . uniqid() . '.jpg';
        $expectedPath = '/' . $this->relativeDir . 'temp/' . $filename;
        
        $this->uploadedFile->expects($this->once())
            ->method('move')
            ->with($this->tempDir, $filename);

        // Act
        $result = $this->processor->process($this->uploadedFile, $filename);

        // Assert
        $this->assertSame($expectedPath, $result);
    }

    public function testProcessCreatesDirectoryIfNotExists(): void
    {
        // Arrange
        $filename = 'test_' . uniqid() . '.jpg';
        $testDir = $this->tempDir . '_new';
        
        $processor = new TempImageProcessor($testDir, $this->relativeDir);
        
        $this->uploadedFile->expects($this->once())
            ->method('move')
            ->with($testDir, $filename);

        // Act
        $result = $processor->process($this->uploadedFile, $filename);

        // Assert
        $this->assertDirectoryExists($testDir);
        $this->assertSame('/' . $this->relativeDir . 'temp/' . $filename, $result);
        
        // Cleanup
        if (is_dir($testDir)) {
            rmdir($testDir);
        }
    }

    public function testProcessThrowsExceptionOnMoveFailure(): void
    {
        // Arrange
        $filename = 'test_' . uniqid() . '.jpg';
        $exceptionMessage = 'Move failed';
        
        $this->uploadedFile->method('move')
            ->willThrowException(new FileException($exceptionMessage));

        // Assert
        $this->expectException(FileException::class);
        $this->expectExceptionMessage($exceptionMessage);

        // Act
        $this->processor->process($this->uploadedFile, $filename);
    }

    public function testProcessThrowsExceptionOnDirectoryCreationFailure(): void
    {
        // Arrange
        $filename = 'test_' . uniqid() . '.jpg';
        $nonWritableDir = '/non/existing/path';
        
        $processor = new TempImageProcessor($nonWritableDir, $this->relativeDir);

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Directory ".*" was not created/');

        // Act
        $processor->process($this->uploadedFile, $filename);
    }
}
