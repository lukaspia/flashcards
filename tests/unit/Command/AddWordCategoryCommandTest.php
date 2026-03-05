<?php

namespace App\Tests\Command;

use App\Command\AddWordCategoryCommand;
use App\Entity\WordCategory;

use App\Service\Word\WordCategoryServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class AddWordCategoryCommandTest extends TestCase
{
    private MockObject|WordCategoryServiceInterface $categoryService;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->categoryService = $this->createMock(WordCategoryServiceInterface::class);

        $command = new AddWordCategoryCommand($this->categoryService);
        $application = new Application();
        $application->add($command);

        $this->commandTester = new CommandTester($application->find('app:add-word-category'));
    }

    public function testExecuteWithArgumentSuccess(): void
    {
        $categoryName = 'Technologia';

        $mockCategory = $this->createMock(WordCategory::class);
        $mockCategory->method('getName')->willReturn($categoryName);
        $mockCategory->method('getId')->willReturn(10);

        $this->categoryService->expects($this->once())
            ->method('createCategory')
            ->with($categoryName)
            ->willReturn($mockCategory);

        $result = $this->commandTester->execute([
                                                    'category_name' => $categoryName
                                                ]);

        $this->assertEquals(Command::SUCCESS, $result);
        $this->assertStringContainsString(
            'Category "Technologia" (ID: 10) created successfully',
            $this->commandTester->getDisplay()
        );
    }

    public function testExecuteWithInteractiveInputSuccess(): void
    {
        $categoryName = 'Podróże';

        $mockCategory = $this->createMock(WordCategory::class);
        $mockCategory->method('getName')->willReturn($categoryName);
        $mockCategory->method('getId')->willReturn(20);

        $this->categoryService->expects($this->once())
            ->method('createCategory')
            ->with($categoryName)
            ->willReturn($mockCategory);

        $this->commandTester->setInputs([$categoryName]);
        $result = $this->commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $result);
        $this->assertStringContainsString('Please enter the word category name', $this->commandTester->getDisplay());
        $this->assertStringContainsString('created successfully', $this->commandTester->getDisplay());
    }

    public function testExecuteFailsWhenEmpty(): void
    {
        $this->commandTester->setInputs([' ']);
        $result = $this->commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $result);
        $this->assertStringContainsString('Category name cannot be empty.', $this->commandTester->getDisplay());

        $this->categoryService->expects($this->never())->method('createCategory');
    }

    public function testExecuteHandlesServiceException(): void
    {
        $this->categoryService->method('createCategory')
            ->willThrowException(new \Exception('Category already exists in MySQL'));

        $result = $this->commandTester->execute([
                                                    'category_name' => 'Duplikat'
                                                ]);

        $this->assertEquals(Command::FAILURE, $result);
        $this->assertStringContainsString('Category already exists in MySQL', $this->commandTester->getDisplay());
    }
}
