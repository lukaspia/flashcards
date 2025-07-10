<?php

declare(strict_types=1);

namespace App\Tests\ImageProcessing\Word;

use App\Entity\Word;
use App\Entity\User;
use App\Entity\Lesson;
use App\ImageProcessing\Word\WordImageProcessor;
use App\Service\Lesson\WordImageServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;

class WordImageProcessorTest extends TestCase
{
    private WordImageProcessor $processor;
    private EntityManagerInterface|MockObject $entityManager;
    private WordImageServiceInterface|MockObject $wordImageService;
    private Word $word;
    private string $uploadDir = '/tmp/word_images';
    private string $relativeDir = 'uploads/word_images';
    private Filesystem $filesystem;
    private User $user;
    private Lesson $lesson;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->wordImageService = $this->createMock(WordImageServiceInterface::class);
        
        // Create a complete Word entity with required relationships
        $this->user = new User();
        $this->user->setId(1);
        
        $this->lesson = new Lesson();
        $this->lesson->setId(1);
        $this->lesson->setUser($this->user);
        
        $this->word = new Word();
        $this->word->setLesson($this->lesson);
        $this->word->setImage(null); // Explicitly set image to null
        
        $this->filesystem = new Filesystem();
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            $this->filesystem->mkdir($this->uploadDir);
        }

        $this->processor = new WordImageProcessor(
            $this->entityManager,
            $this->wordImageService,
            $this->uploadDir,
            $this->relativeDir,
            $this->word
        );
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (is_dir($this->uploadDir)) {
            $this->filesystem->remove($this->uploadDir);
        }
    }

    public function testProcessNewImage(): void
    {
        // Arrange
        $filename = 'test_image.jpg';
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'test content');
        
        $uploadedFile = new UploadedFile(
            $tempFile,
            'original_name.jpg',
            'image/jpeg',
            null,
            true // Test mode - don't move the file yet
        );

        // Mock EntityManager expectations
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->word);
            
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $result = $this->processor->process($uploadedFile, $filename);

        // Assert
        $expectedPath = '/' . $this->relativeDir . '1/1/' . $filename;
        $this->assertEquals($expectedPath, $result);
        $this->assertEquals($expectedPath, $this->word->getImage());
        
        // The actual file should be in the upload directory with the correct path
        $expectedFilePath = $this->uploadDir . '1/1/' . $filename;
        $this->assertFileExists($expectedFilePath);
        
        // Clean up - only remove the moved file, not the original temp file
        if (file_exists($expectedFilePath)) {
            unlink($expectedFilePath);
        }
    }

    public function testProcessReplacesExistingImage(): void
    {
        // Arrange
        $oldImage = '/uploads/word_images1/1/old_image.jpg';
        $newImage = 'new_image.jpg';
        $this->word->setImage($oldImage);

        // Create a mock UploadedFile that doesn't actually interact with the filesystem
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getClientOriginalName')
            ->willReturn('test.jpg');
        $uploadedFile->method('guessExtension')
            ->willReturn('jpg');
        $uploadedFile->method('move')
            ->willReturnCallback(function ($directory, $name) {
                // Create the directory if it doesn't exist
                if (!is_dir($directory)) {
                    mkdir($directory, 0777, true);
                }
                // Create an empty file at the target location
                $target = $directory . '/' . $name;
                touch($target);
                return new \Symfony\Component\HttpFoundation\File\File($target);
            });

        // Mock WordImageService to expect removal of old image
        $this->wordImageService->expects($this->once())
            ->method('removeWordImageFile')
            ->with($this->word);
            
        // Mock EntityManager expectations
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->word);
            
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $result = $this->processor->process($uploadedFile, $newImage);

        // Assert
        $expectedPath = '/' . $this->relativeDir . '1/1/' . $newImage;
        $this->assertEquals($expectedPath, $result);
        $this->assertEquals($expectedPath, $this->word->getImage());
        
        // The actual file should be in the upload directory with the correct path
        $expectedFilePath = $this->uploadDir . '1/1/' . $newImage;
        $this->assertFileExists($expectedFilePath);
    }

    public function testProcessThrowsExceptionWhenDirectoryCreationFails(): void
    {
        // Arrange
        $filename = 'test_image.jpg';
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'test content');
        
        $uploadedFile = new UploadedFile(
            $tempFile,
            'original_name.jpg',
            'image/jpeg',
            null,
            true
        );

        // Create a processor with a non-writable directory
        $nonWritableDir = '/non/existing/path';
        $processor = new WordImageProcessor(
            $this->entityManager,
            $this->wordImageService,
            $nonWritableDir,
            $this->relativeDir,
            $this->word
        );

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Directory ".*" was not created/');

        // Act
        $processor->process($uploadedFile, $filename);
        
        // Clean up
        unlink($tempFile);
    }

    public function testProcessWrapsExceptions(): void
    {
        // Arrange
        $filename = 'test_image.jpg';
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'test content');
        
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getClientOriginalName')
            ->willReturn('test.jpg');
        $uploadedFile->method('guessExtension')
            ->willReturn('jpg');
        $uploadedFile->method('move')
            ->willThrowException(new \Exception('Move failed'));

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to process word image: Move failed');

        // Act
        $this->processor->process($uploadedFile, $filename);
        
        // Clean up
        unlink($tempFile);
    }
}
