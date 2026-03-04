<?php

namespace App\Tests\Command;

use App\Command\DeleteUserCommand;

use App\Service\User\UserServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class DeleteUserCommandTest extends TestCase
{
    private MockObject|UserServiceInterface $userService;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserServiceInterface::class);
        $command = new DeleteUserCommand($this->userService);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithArgumentSuccess(): void
    {
        $username = 'jan_kowalski';

        $this->userService->expects($this->once())
            ->method('deleteUser')
            ->with($username);

        $result = $this->commandTester->execute([
                                                    'username' => $username
                                                ]);

        $this->assertEquals(Command::SUCCESS, $result);
        $this->assertStringContainsString(
            sprintf('User "%s" has been successfully deleted.', $username),
            $this->commandTester->getDisplay()
        );
    }

    public function testExecuteWithInteractiveInputSuccess(): void
    {
        $username = 'anna_nowak';

        $this->userService->expects($this->once())
            ->method('deleteUser')
            ->with($username);

        $this->commandTester->setInputs([$username]);
        $result = $this->commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $result);
        $this->assertStringContainsString('Please enter the username', $this->commandTester->getDisplay());
        $this->assertStringContainsString('successfully deleted', $this->commandTester->getDisplay());
    }

    public function testExecuteFailsWhenUsernameEmpty(): void
    {
        $this->commandTester->setInputs(['']);
        $result = $this->commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $result);
        $this->assertStringContainsString('Username cannot be empty.', $this->commandTester->getDisplay());

        $this->userService->expects($this->never())->method('deleteUser');
    }

    public function testExecuteHandlesServiceException(): void
    {
        $username = 'nieistniejacy_user';

        $this->userService->method('deleteUser')
            ->willThrowException(new \Exception('User not found.'));

        $result = $this->commandTester->execute([
                                                    'username' => $username
                                                ]);

        $this->assertEquals(Command::FAILURE, $result);
        $display = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Error deleting user.', $display);
        $this->assertStringContainsString('User not found.', $display);
    }
}
