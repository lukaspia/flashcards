<?php

namespace App\Tests\Command;

use App\Command\DeleteWordCategoryCommand;
use App\Service\Word\WordCategoryServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class DeleteWordCategoryCommandTest extends TestCase
{
    private MockObject|WordCategoryServiceInterface $categoryService;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->categoryService = $this->createMock(WordCategoryServiceInterface::class);
        $command = new DeleteWordCategoryCommand($this->categoryService);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithArgumentSuccess(): void
    {
        $categoryName = 'Owoce';

        $this->categoryService->expects($this->once())
            ->method('deleteCategory')
            ->with($categoryName);

        $result = $this->commandTester->execute([
                                                    'category_name' => $categoryName
                                                ]);

        $this->assertEquals(Command::SUCCESS, $result);
        $this->assertStringContainsString(
            sprintf('Category "%s" deleted successfully.', $categoryName),
            $this->commandTester->getDisplay()
        );
    }

    public function testExecuteWithInteractiveInputSuccess(): void
    {
        $categoryName = 'Warzywa';

        $this->categoryService->expects($this->once())
            ->method('deleteCategory')
            ->with($categoryName);

        $this->commandTester->setInputs([$categoryName]);
        $result = $this->commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $result);
        $this->assertStringContainsString(
            'Please enter the name of the category to delete',
            $this->commandTester->getDisplay()
        );
    }

    public function testExecuteFailsWhenCategoryNameEmpty(): void
    {
        $this->commandTester->setInputs(['']);
        $result = $this->commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $result);
        $this->assertStringContainsString('Category name cannot be empty.', $this->commandTester->getDisplay());

        $this->categoryService->expects($this->never())->method('deleteCategory');
    }

    public function testExecuteHandlesServiceException(): void
    {
        $categoryName = 'Nieistniejąca';

        $this->categoryService->method('deleteCategory')
            ->willThrowException(new \Exception('Category not found in database.'));

        $result = $this->commandTester->execute([
                                                    'category_name' => $categoryName
                                                ]);

        $this->assertEquals(Command::FAILURE, $result);
        $this->assertStringContainsString('Category not found in database.', $this->commandTester->getDisplay());
    }
}