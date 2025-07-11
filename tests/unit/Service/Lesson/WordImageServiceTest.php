<?php

declare(strict_types=1);

namespace App\Tests\Service\Lesson;

use App\Entity\Word;
use App\Entity\Lesson;
use App\Entity\User;
use App\File\FileManagerInterface;
use App\Service\Lesson\WordImageService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\Common\Collections\ArrayCollection;

class WordImageServiceTest extends TestCase
{
    private WordImageService $wordImageService;
    private MockObject|EntityManagerInterface $entityManager;
    private MockObject|ParameterBagInterface $parameterBag;
    private MockObject|FileManagerInterface $fileManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);
        $this->fileManager = $this->createMock(FileManagerInterface::class);

        $this->wordImageService = new WordImageService(
            $this->entityManager,
            $this->parameterBag,
            $this->fileManager
        );
    }

    private function createWordWithLesson(string $image = null): Word
    {
        $user = new User();
        $user->setId(1);
        
        $lesson = new Lesson();
        $lesson->setId(1);
        $lesson->setUser($user);
        
        $word = new Word();
        $word->setLesson($lesson);
        $word->setBasicWord('test');
        $word->setTranslation('test');
        $word->setExample('test');
        $word->setColor('#000000');
        
        if ($image !== null) {
            $word->setImage($image);
        }
        
        return $word;
    }

    public function testGetWordImageFilePathWithNoImage(): void
    {
        $word = $this->createWordWithLesson();
        $word->setImage(null);

        $result = $this->wordImageService->getWordImageFilePath($word);
        $this->assertNull($result);
    }

    public function testGetWordImageFilePathWithImage(): void
    {
        $word = $this->createWordWithLesson('/uploads/images/test.jpg');

        $this->parameterBag->method('get')
            ->with('public_dir')
            ->willReturn('/var/www/public');

        $result = $this->wordImageService->getWordImageFilePath($word);
        $this->assertEquals('/var/www/public/uploads/images/test.jpg', $result);
    }

    public function testRemoveWordImageWithNoImage(): void
    {
        $word = $this->createWordWithLesson();
        $word->setImage(null);

        $result = $this->wordImageService->removeWordImage($word);
        $this->assertFalse($result);
    }

    public function testRemoveWordImageSuccessfully(): void
    {
        $word = $this->createWordWithLesson('/uploads/images/test.jpg');

        $this->parameterBag->method('get')
            ->with('public_dir')
            ->willReturn('/var/www/public');

        $this->fileManager->expects($this->once())
            ->method('removeFile')
            ->with('/var/www/public/uploads/images/test.jpg')
            ->willReturn(true);

        $this->entityManager->expects($this->once())
            ->method('beginTransaction');

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($word);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->entityManager->expects($this->once())
            ->method('commit');

        $result = $this->wordImageService->removeWordImage($word);
        $this->assertTrue($result);
        $this->assertNull($word->getImage());
    }

    public function testRemoveWordImageRollsBackOnFailure(): void
    {
        $word = $this->createWordWithLesson('/uploads/images/test.jpg');

        $this->parameterBag->method('get')
            ->with('public_dir')
            ->willReturn('/var/www/public');

        $this->fileManager->method('removeFile')
            ->willThrowException(new \RuntimeException('File not found'));

        $this->entityManager->expects($this->once())
            ->method('beginTransaction');

        $this->entityManager->expects($this->once())
            ->method('rollback');

        $this->entityManager->expects($this->never())
            ->method('commit');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to remove word image: Failed to remove image file for word ID : File not found');

        $this->wordImageService->removeWordImage($word);
    }

    public function testRemoveWordImageFileSuccessfully(): void
    {
        $word = $this->createWordWithLesson('/uploads/images/test.jpg');

        $this->parameterBag->method('get')
            ->with('public_dir')
            ->willReturn('/var/www/public');

        $this->fileManager->expects($this->once())
            ->method('removeFile')
            ->with('/var/www/public/uploads/images/test.jpg')
            ->willReturn(true);

        $result = $this->wordImageService->removeWordImageFile($word);
        $this->assertTrue($result);
    }

    public function testRemoveWordImageFileWithNoImage(): void
    {
        $word = $this->createWordWithLesson();
        $word->setImage(null);

        $result = $this->wordImageService->removeWordImageFile($word);
        $this->assertFalse($result);
    }

    public function testMoveWordsImagesFromTemporary(): void
    {
        $word1 = $this->createWordWithLesson('/temporary/old1.jpg');
        $word2 = $this->createWordWithLesson('/temporary/old2.jpg');
        $words = new ArrayCollection([$word1, $word2]);

        $this->parameterBag->method('get')
            ->willReturnMap([
                ['word_image_upload_dir_temp', '/tmp/'],
                ['word_image_upload_dir', '/var/www/public/uploads/word_images/'],
                ['word_image_upload_dir_relative', 'uploads/word_images/']
            ]);

        $this->fileManager->expects($this->exactly(2))
            ->method('moveFile')
            ->willReturnCallback(function ($source, $target) {
                $this->assertStringContainsString('/tmp/old', $source);
                $this->assertStringContainsString('/var/www/public/uploads/word_images/1/1/old', $target);
                return true;
            });

        $this->entityManager->expects($this->exactly(2))
            ->method('persist')
            ->with($this->callback(function($word) {
                /** @var Word $word */
                return $word->getImage() === '/uploads/word_images/1/1/old1.jpg' || 
                       $word->getImage() === '/uploads/word_images/1/1/old2.jpg';
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->wordImageService->moveWordsImagesFromTemporary($words);
    }
}
