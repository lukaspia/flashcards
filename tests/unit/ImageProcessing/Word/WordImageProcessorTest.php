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

        $this->user = new User();
        $this->user->setId(1);
        
        $this->lesson = new Lesson();
        $this->lesson->setId(1);
        $this->lesson->setUser($this->user);
        
        $this->word = new Word();
        $this->word->setLesson($this->lesson);
        $this->word->setImage(null);
        
        $this->filesystem = new Filesystem();

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
        if (is_dir($this->uploadDir)) {
            $this->filesystem->remove($this->uploadDir);
        }
    }

    public function testProcessNewImage(): void
    {
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

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->word);
            
        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->processor->process($uploadedFile, $filename);

        $expectedPath = '/' . $this->relativeDir . '1/1/' . $filename;
        $this->assertEquals($expectedPath, $result);
        $this->assertEquals($expectedPath, $this->word->getImage());

        $expectedFilePath = $this->uploadDir . '1/1/' . $filename;
        $this->assertFileExists($expectedFilePath);

        if (file_exists($expectedFilePath)) {
            unlink($expectedFilePath);
        }
    }

    public function testProcessReplacesExistingImage(): void
    {
        $oldImage = '/uploads/word_images1/1/old_image.jpg';
        $newImage = 'new_image.jpg';
        $this->word->setImage($oldImage);

        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getClientOriginalName')
            ->willReturn('test.jpg');
        $uploadedFile->method('guessExtension')
            ->willReturn('jpg');
        $uploadedFile->method('move')
            ->willReturnCallback(function ($directory, $name) {
                if (!is_dir($directory)) {
                    mkdir($directory, 0777, true);
                }
                $target = $directory . '/' . $name;
                touch($target);
                return new \Symfony\Component\HttpFoundation\File\File($target);
            });

        $this->wordImageService->expects($this->once())
            ->method('removeWordImageFile')
            ->with($this->word);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->word);
            
        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->processor->process($uploadedFile, $newImage);

        $expectedPath = '/' . $this->relativeDir . '1/1/' . $newImage;
        $this->assertEquals($expectedPath, $result);
        $this->assertEquals($expectedPath, $this->word->getImage());

        $expectedFilePath = $this->uploadDir . '1/1/' . $newImage;
        $this->assertFileExists($expectedFilePath);
    }

    public function testProcessThrowsExceptionWhenDirectoryCreationFails(): void
    {
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

        $nonWritableDir = '/non/existing/path';
        $processor = new WordImageProcessor(
            $this->entityManager,
            $this->wordImageService,
            $nonWritableDir,
            $this->relativeDir,
            $this->word
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Directory ".*" was not created/');

        $processor->process($uploadedFile, $filename);

        unlink($tempFile);
    }

    public function testProcessWrapsExceptions(): void
    {
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

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to process word image: Move failed');

        $this->processor->process($uploadedFile, $filename);

        unlink($tempFile);
    }
}
