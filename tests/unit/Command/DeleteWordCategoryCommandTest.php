<?php

namespace App\Tests\Command;

use App\Command\DeleteWordCategoryCommand;
use App\Entity\WordCategory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class DeleteWordCategoryCommandTest extends TestCase
{
    private $entityManager;
    private $repository;
    private $commandTester;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);

        $this->entityManager->method('getRepository')->willReturn($this->repository);

        $command = new DeleteWordCategoryCommand($this->entityManager);
        $this->commandTester = new CommandTester($command);
    }

    public function testSuccessWhenCategoryExistsAndProvidedAsArgument(): void
    {
        $categoryName = 'TestCategory';
        $wordCategory = new WordCategory();
        $wordCategory->setName($categoryName);

        $this->repository->expects($this->once())
            ->method('findBy')
            ->with(['name' => $categoryName])
            ->willReturn([$wordCategory]);

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($wordCategory);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->commandTester->execute([
            'category_name' => $categoryName,
        ]);

        $this->commandTester->assertCommandIsSuccessful();
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Category deleted successfully', $output);
    }

    public function testSuccessWhenCategoryExistsAndProvidedInteractively(): void
    {
        $categoryName = 'TestCategory';
        $wordCategory = new WordCategory();
        $wordCategory->setName($categoryName);

        $this->repository->expects($this->once())
            ->method('findBy')
            ->with(['name' => $categoryName])
            ->willReturn([$wordCategory]);

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($wordCategory);

        $this->entityManager->expects($this->once())
            ->method('flush');

        // We don't provide the argument, so the command will ask
        $this->commandTester->setInputs([$categoryName]);
        $this->commandTester->execute([]);

        $this->commandTester->assertCommandIsSuccessful();
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Word category name', $output); // The question
        $this->assertStringContainsString('Category deleted successfully', $output);
    }

    public function testFailureWhenCategoryDoesNotExistAndProvidedAsArgument(): void
    {
        $categoryName = 'NonExistentCategory';

        $this->repository->expects($this->once())
            ->method('findBy')
            ->with(['name' => $categoryName])
            ->willReturn([]);

        $this->entityManager->expects($this->never())
            ->method('remove');
        $this->entityManager->expects($this->never())
            ->method('flush');

        $this->commandTester->execute([
            'category_name' => $categoryName,
        ]);

        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Category not found', $output);
    }

    public function testFailureWhenCategoryDoesNotExistAndProvidedInteractively(): void
    {
        $categoryName = 'NonExistentCategory';

        $this->repository->expects($this->once())
            ->method('findBy')
            ->with(['name' => $categoryName])
            ->willReturn([]);

        $this->entityManager->expects($this->never())
            ->method('remove');
        $this->entityManager->expects($this->never())
            ->method('flush');

        $this->commandTester->setInputs([$categoryName]);
        $this->commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Category not found', $output);
    }
}
