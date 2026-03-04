<?php

namespace App\Tests\ImageProcessing\Word;

use App\Entity\Word;
use App\File\FileManagerInterface;
use App\File\FileNameGeneratorInterface;
use App\ImageProcessing\Word\WordImageProcessor;
use App\Service\Lesson\WordImageServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class WordImageProcessorTest extends TestCase
{
    private MockObject|WordImageServiceInterface $wordServices;
    private MockObject|FileNameGeneratorInterface $fileNameGenerator;
    private MockObject|FileManagerInterface $fileManager;
    private WordImageProcessor $processor;

    private const UPLOAD_DIR = '/var/www/uploads';
    private const TEMP_DIR = '/var/www/uploads/temp';
    private const RELATIVE_DIR = 'uploads/words';

    protected function setUp(): void
    {
        $this->wordServices = $this->createMock(WordImageServiceInterface::class);
        $this->fileNameGenerator = $this->createMock(FileNameGeneratorInterface::class);
        $this->fileManager = $this->createMock(FileManagerInterface::class);

        $this->processor = new WordImageProcessor(
            $this->wordServices,
            $this->fileNameGenerator,
            $this->fileManager,
            self::UPLOAD_DIR,
            self::TEMP_DIR,
            self::RELATIVE_DIR
        );
    }

    public function testProcessTemporary(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('guessExtension')->willReturn('jpg');

        $this->fileManager->expects($this->once())
            ->method('upload')
            ->with($file, self::TEMP_DIR, $this->callback(fn($name) => str_starts_with($name, 'temp_')));

        $result = $this->processor->process($file, null);

        $this->assertStringContainsString('/uploads/words/temp/temp_', $result);
        $this->assertStringEndsWith('.jpg', $result);
    }

    public function testProcessPermanentWithExistingImage(): void
    {
        $word = $this->createMock(Word::class);
        $word->method('getId')->willReturn(123);
        $word->method('getImage')->willReturn('/old/path.jpg');

        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalName')->willReturn('new-image.png');

        $this->wordServices->expects($this->once())
            ->method('removeWordImageFile')
            ->with($word);

        $this->fileNameGenerator->method('generate')->willReturn('hashed_name.png');
        $this->wordServices->method('generateRelativePath')->willReturn('user_1/lesson_2/');

        $expectedFullDir = self::UPLOAD_DIR . '/user_1/lesson_2/';
        $this->fileManager->expects($this->once())
            ->method('upload')
            ->with($file, $expectedFullDir, 'hashed_name.png');

        $word->expects($this->once())
            ->method('setImage')
            ->with('/uploads/words/user_1/lesson_2/hashed_name.png');

        $this->processor->process($file, $word);
    }
}