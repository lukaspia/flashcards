<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\Word;
use App\Factory\ImageProcessorFactory;
use App\ImageProcessing\Word\TempImageProcessor;
use App\ImageProcessing\Word\WordImageProcessor;
use App\ImageProcessing\Word\WordImageProcessorInterface;
use App\Service\Lesson\WordImageServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class ImageProcessorFactoryTest extends TestCase
{
    private $entityManager;
    private $wordServices;
    private $factory;
    private $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->wordServices = $this->createMock(WordImageServiceInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);

        $this->entityManager->method('getRepository')->willReturn($this->repository);

        $this->factory = new ImageProcessorFactory(
            $this->entityManager,
            $this->wordServices,
            '/upload/dir',
            '/relative/upload/dir',
            '/temp/dir'
        );
    }

    public function testCreateProcessorWhenWordFound(): void
    {
        $wordId = 1;
        $word = $this->createMock(Word::class);

        $this->repository->method('find')->with($wordId)->willReturn($word);

        $processor = $this->factory->createProcessor($wordId);

        $this->assertInstanceOf(WordImageProcessor::class, $processor);
    }

    public function testCreateProcessorWhenWordNotFound(): void
    {
        $wordId = 1;

        $this->repository->method('find')->with($wordId)->willReturn(null);

        $processor = $this->factory->createProcessor($wordId);

        $this->assertInstanceOf(TempImageProcessor::class, $processor);
    }
}
