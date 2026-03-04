<?php

namespace App\Tests\Service\Lesson;

use App\Entity\Lesson;
use App\Entity\User;
use App\Entity\Word;
use App\File\FileManagerInterface;
use App\Service\Lesson\WordImageService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WordImageServiceTest extends TestCase
{
    private MockObject|EntityManagerInterface $entityManager;
    private MockObject|FileManagerInterface $fileManager;
    private WordImageService $service;

    private const PUBLIC_DIR = '/var/www/public';
    private const TEMP_DIR = '/var/www/public/uploads/temp';
    private const UPLOAD_DIR = '/var/www/public/uploads/words';
    private const RELATIVE_DIR = 'uploads/words';

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->fileManager = $this->createMock(FileManagerInterface::class);

        $this->entityManager->method('wrapInTransaction')
            ->will($this->returnCallback(fn($callback) => $callback($this->entityManager)));

        $this->service = new WordImageService(
            $this->entityManager,
            $this->fileManager,
            self::PUBLIC_DIR,
            self::TEMP_DIR,
            self::UPLOAD_DIR,
            self::RELATIVE_DIR
        );
    }

    public function testGetWordImageFilePath(): void
    {
        $word = $this->createMock(Word::class);
        $word->method('getImage')->willReturn('/uploads/words/1/1/file.jpg');

        $result = $this->service->getWordImageFilePath($word);

        $this->assertEquals('/var/www/public/uploads/words/1/1/file.jpg', $result);
    }

    public function testGenerateRelativePathSuccess(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(10);

        $lesson = $this->createMock(Lesson::class);
        $lesson->method('getId')->willReturn(25);
        $lesson->method('getUser')->willReturn($user);

        $word = $this->createMock(Word::class);
        $word->method('getLesson')->willReturn($lesson);

        $result = $this->service->generateRelativePath($word);

        $this->assertEquals('10/25/', $result);
    }

    public function testMoveWordsImagesFromTemporary(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(10);
        $lesson = $this->createMock(Lesson::class);
        $lesson->method('getId')->willReturn(25);
        $lesson->method('getUser')->willReturn($user);

        $word = $this->createMock(Word::class);

        $word->method('getImage')->willReturn('/media/temp/temp_name.jpg');
        $word->method('getLesson')->willReturn($lesson);

        $this->fileManager->expects($this->once())
            ->method('moveFile')
            ->willReturn(true);

        $word->expects($this->once())
            ->method('setImage')
            ->with($this->stringContains('/uploads/words/10/25/temp_name.jpg'));

        $this->entityManager->expects($this->once())->method('flush');

        $this->service->moveWordsImagesFromTemporary(new ArrayCollection([$word]));
    }

    public function testMoveWordsImagesDoesNothingIfAlreadyProcessed(): void
    {
        $word = $this->createMock(Word::class);

        $word->method('getImage')->willReturn('/uploads/words/10/25/existing.jpg');

        $this->fileManager->expects($this->never())->method('moveFile');
        $this->entityManager->expects($this->never())->method('flush');

        $this->service->moveWordsImagesFromTemporary(new ArrayCollection([$word]));
    }
}