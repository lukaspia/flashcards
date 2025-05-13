<?php

namespace App\Tests\Command;

use App\Command\DeleteUserCommand;
use App\Service\Response\OperationResponse;
use App\Service\User\UserService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DeleteUserCommandTest extends TestCase
{
    private UserService $userService;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserService::class);

        $command = new DeleteUserCommand($this->userService);

        $application = new Application();
        $application->add($command);

        $this->commandTester = new CommandTester($command);
    }

    public function testCommandConfiguration(): void
    {
        $application = new Application();
        $command = new DeleteUserCommand($this->userService);
        $application->add($command);

        $this->assertEquals('app:delete-user', $command->getName());
        $this->assertEquals(['app:remove-user'], $command->getAliases());
        $this->assertEquals('Delete users from database', $command->getDescription());
        $this->assertTrue($command->getDefinition()->hasArgument('username'));
    }

    public function testSuccessfulUserDeletion(): void
    {
        $username = 'test_user';

        $successResponse = new OperationResponse(true, 'User deleted successfully');

        $this->userService
            ->expects($this->once())
            ->method('deleteUser')
            ->with($username)
            ->willReturn($successResponse);

        $this->commandTester->execute([
                                          'username' => $username,
                                      ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('User deleted successfully', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testFailedUserDeletion(): void
    {
        $username = 'nonexistent_user';

        $failureResponse = new OperationResponse(false, 'User not found');

        $this->userService
            ->expects($this->once())
            ->method('deleteUser')
            ->with($username)
            ->willReturn($failureResponse);

        $this->commandTester->execute([
                                          'username' => $username,
                                      ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Error deleting user', $output);
        $this->assertStringContainsString('User not found', $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    public function testInteractiveUsernameInput(): void
    {
        $username = 'interactive_user';

        $successResponse = new OperationResponse(true, 'User deleted successfully');

        $this->userService
            ->expects($this->once())
            ->method('deleteUser')
            ->with($username)
            ->willReturn($successResponse);

        $this->commandTester->setInputs([$username]);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Username', $output);
        $this->assertStringContainsString('User deleted successfully', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
