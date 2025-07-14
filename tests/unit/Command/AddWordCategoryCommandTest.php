<?php

namespace App\Tests\Command;

use App\Command\AddWordCategoryCommand;
use App\Entity\WordCategory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class AddWordCategoryCommandTest extends TestCase
{
    private $entityManager;
    private $commandTester;
    private $command;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $command = new AddWordCategoryCommand($this->entityManager);
        
        $application = new Application();
        $application->add($command);
        
        $this->command = $application->find('app:add-word-category');
        $this->commandTester = new CommandTester($this->command);
    }

    public function testCommandConfiguration()
    {
        $this->assertSame('app:add-word-category', $this->command->getName());
        $this->assertSame('Creates word category and stores it in the database', $this->command->getDescription());
        $this->assertTrue($this->command->getDefinition()->hasArgument('category_name'));
        $argument = $this->command->getDefinition()->getArgument('category_name');
        $this->assertFalse($argument->isRequired());
        $this->assertSame('The name of the new word category', $argument->getDescription());
    }

    public function testExecuteWithCategoryNameArgument()
    {
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function($wordCategory) {
                return $wordCategory instanceof WordCategory && 
                       $wordCategory->getName() === 'TestCategory';
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->commandTester->execute([
            'category_name' => 'TestCategory',
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Category created successfully', $output);
    }

    public function testExecuteWithInteractiveInput()
    {
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function($wordCategory) {
                return $wordCategory instanceof WordCategory && 
                       $wordCategory->getName() === 'InteractiveCategory';
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->commandTester->setInputs(['InteractiveCategory']);
        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Word category name', $output);
        $this->assertStringContainsString('Category created successfully', $output);
    }

    public function testExecuteWithEmptyCategoryName()
    {
        $this->entityManager->expects($this->never())
            ->method('persist');
            
        $this->entityManager->expects($this->never())
            ->method('flush');

        $this->commandTester->setInputs(['']);
        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Error: Category name cannot be empty.', $output);
    }

    public function testExecuteWithWhitespaceCategoryName()
    {
        $this->entityManager->expects($this->never())
            ->method('persist');
            
        $this->entityManager->expects($this->never())
            ->method('flush');

        $this->commandTester->setInputs(['   ']);
        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Error: Category name cannot be empty.', $output);
    }
}
