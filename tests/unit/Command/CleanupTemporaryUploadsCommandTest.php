<?php

namespace App\Tests\Command;

use App\Command\CleanupTemporaryUploadsCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CleanupTemporaryUploadsCommandTest extends TestCase
{
    private $parameterBagMock;
    private $command;
    private $commandTester;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set default timezone to UTC
        date_default_timezone_set('UTC');
        
        $this->parameterBagMock = $this->createMock(ParameterBagInterface::class);
        $this->command = new CleanupTemporaryUploadsCommand($this->parameterBagMock);
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecuteFailsWhenDirectoryDoesNotExist()
    {
        $this->parameterBagMock->method('get')
            ->with('word_image_upload_dir_temp')
            ->willReturn('/nonexistent/dir');

        $this->commandTester->execute([]);
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('Starting temporary upload cleanup...', $output);
        $this->assertEquals(CleanupTemporaryUploadsCommand::FAILURE, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithEmptyDirectory()
    {
        $tempDir = sys_get_temp_dir() . '/flashcards_test_' . uniqid();
        mkdir($tempDir);

        $this->parameterBagMock->method('get')
            ->with('word_image_upload_dir_temp')
            ->willReturn($tempDir);

        $this->commandTester->execute([]);
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('Starting temporary upload cleanup...', $output);
        $this->assertStringContainsString('Deleted files: 0', $output);
        $this->assertEquals(CleanupTemporaryUploadsCommand::SUCCESS, $this->commandTester->getStatusCode());

        rmdir($tempDir);
    }

    public function testExecuteHandlesDeletionFailure()
    {
        $tempDir = sys_get_temp_dir() . '/flashcards_test_' . uniqid();
        mkdir($tempDir);
        
        $protectedFile = $tempDir . '/protected_file.txt';
        touch($protectedFile, time() - 36000);
        chmod($tempDir, 0555);

        $this->parameterBagMock->method('get')
            ->with('word_image_upload_dir_temp')
            ->willReturn($tempDir);

        $this->commandTester->execute([]);
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('Starting temporary upload cleanup...', $output);
        $this->assertStringContainsString('Failed to delete file', $output);
        $this->assertEquals(CleanupTemporaryUploadsCommand::SUCCESS, $this->commandTester->getStatusCode());

        chmod($tempDir, 0755);
        unlink($protectedFile);
        rmdir($tempDir);
    }

    public function testIsFileCreatedBeforeDateWithInvalidDate()
    {
        $command = new CleanupTemporaryUploadsCommand($this->parameterBagMock);
        
        $reflection = new \ReflectionClass(CleanupTemporaryUploadsCommand::class);
        $method = $reflection->getMethod('isFileCreatedBeforeDate');
        $method->setAccessible(true);
        
        $testFile = tempnam(sys_get_temp_dir(), 'test_file');
        
        $result = $method->invokeArgs($command, [$testFile, 'invalid-date']);
        $this->assertFalse($result);
        
        unlink($testFile);
    }

    public function testIsFileCreatedBeforeDate()
    {
        // Set default timezone to UTC for consistent date handling
        date_default_timezone_set('UTC');

        $command = new CleanupTemporaryUploadsCommand($this->createMock(ParameterBagInterface::class));

        // Use reflection to access private method
        $reflection = new \ReflectionClass(CleanupTemporaryUploadsCommand::class);
        $method = $reflection->getMethod('isFileCreatedBeforeDate');
        $method->setAccessible(true);

        // Create a temporary file
        $tempDir = sys_get_temp_dir() . '/flashcards_test_' . uniqid();
        mkdir($tempDir);
        $filePath = $tempDir . '/test_file.txt';
        touch($filePath);

        // Use a threshold of '1970-01-01 00:00:00' (very old) for the first test
        $thresholdOld = '1970-01-01 00:00:00';
        // All files will be after this threshold, so the method should return false
        $this->assertFalse($method->invokeArgs($command, [$filePath, $thresholdOld]), 'File should not be before '.$thresholdOld);

        // Use a threshold of '2100-01-01 00:00:00' (very new) for the second test
        $thresholdNew = '2100-01-01 00:00:00';
        // All files will be before this threshold, so the method should return true
        $this->assertTrue($method->invokeArgs($command, [$filePath, $thresholdNew]), 'File should be before '.$thresholdNew);

        // Test nonexistent file
        $this->assertFalse($method->invokeArgs($command, ['/nonexistent/file', $thresholdOld]));

        // Cleanup
        unlink($filePath);
        rmdir($tempDir);
    }
}
