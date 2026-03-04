<?php

namespace App\Tests\Command;

use App\Command\AddUserCommand;
use App\Entity\User;

use App\Service\User\UserServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddUserCommandTest extends TestCase
{
    private MockObject|UserServiceInterface $userService;
    private MockObject|ValidatorInterface $validator;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserServiceInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $command = new AddUserCommand($this->userService, $this->validator);
        $application = new Application();
        $application->add($command);

        $this->commandTester = new CommandTester($application->find('app:add-user'));
    }

    public function testExecuteSuccess(): void
    {
        $this->validator->method('validatePropertyValue')
            ->willReturn(new ConstraintViolationList());

        $mockUser = $this->createMock(User::class);
        $mockUser->method('getUsername')->willReturn('test_user');
        $mockUser->method('getId')->willReturn(1);

        $this->userService->expects($this->once())
            ->method('addUser')
            ->with('test_user', 'password123', false)
            ->willReturn($mockUser);

        $this->commandTester->setInputs(['test_user', 'password123', 'password123']);
        $result = $this->commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $result);
        $this->assertStringContainsString('New test_user user successfully created', $this->commandTester->getDisplay());
    }

    public function testExecutePasswordsDoNotMatch(): void
    {
        $this->validator->expects($this->atLeastOnce())
            ->method('validatePropertyValue')
            ->with(User::class, 'username', 'admin')
            ->willReturn(new ConstraintViolationList());

        $this->commandTester->setInputs(['admin', 'pass1', 'pass2']);
        $result = $this->commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $result);
        $this->assertStringContainsString('Passwords do not match', $this->commandTester->getDisplay());

        $this->userService->expects($this->never())->method('addUser');
    }

    public function testExecuteAdminOption(): void
    {
        $this->validator->method('validatePropertyValue')->willReturn(new ConstraintViolationList());

        $mockUser = $this->createMock(User::class);
        $this->userService->expects($this->once())
            ->method('addUser')
            ->with('admin_user', 'secret', true)
            ->willReturn($mockUser);

        $this->commandTester->setInputs(['admin_user', 'secret', 'secret']);
        $this->commandTester->execute(['--admin' => true]);

        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }
}
