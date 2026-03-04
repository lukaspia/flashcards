<?php

namespace App\Tests\Command;

use App\Command\CleanupTemporaryUploadsCommand;
use App\File\FileManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CleanupTemporaryUploadsCommandTest extends TestCase
{
    private MockObject|FileManagerInterface $fileManager;
    private string $uploadDirTemp = '/tmp/uploads';
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->fileManager = $this->createMock(FileManagerInterface::class);

        $command = new CleanupTemporaryUploadsCommand(
            $this->fileManager,
            $this->uploadDirTemp
        );

        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteSuccess(): void
    {
        $this->fileManager->expects($this->once())
            ->method('cleanupOldFiles')
            ->with($this->uploadDirTemp, 5)
            ->willReturn(15); // Symulujemy usunięcie 15 plików

        $result = $this->commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $result);
        $display = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Starting cleanup in: /tmp/uploads', $display);
        $this->assertStringContainsString('Cleanup finished. Deleted 15 files.', $display);
    }

    public function testExecuteHandlesException(): void
    {
        $this->fileManager->method('cleanupOldFiles')
            ->willThrowException(new \Exception('Permission denied'));

        $result = $this->commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $result);
        $display = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Cleanup failed: Permission denied', $display);
    }

    public function testExecuteHandlesZeroFilesDeleted(): void
    {
        $this->fileManager->method('cleanupOldFiles')
            ->willReturn(0);

        $result = $this->commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $result);
        $this->assertStringContainsString('Deleted 0 files.', $this->commandTester->getDisplay());
    }
}
