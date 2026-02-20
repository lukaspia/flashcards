<?php

namespace App\Tests\Command;

use App\Command\AddUserCommand;
use App\DTO\OperationResponseDTO;
use App\Entity\User;
use App\Service\User\UserServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddUserCommandTest extends TestCase
{
    private UserServiceInterface $userService;
    private ValidatorInterface $validator;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserServiceInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $command = new AddUserCommand($this->userService, $this->validator);

        $application = new Application();
        $application->add($command);

        $this->commandTester = new CommandTester($command);
    }

    public function testCommandConfiguration(): void
    {
        $application = new Application();
        $command = new AddUserCommand($this->userService, $this->validator);
        $application->add($command);

        $command = $application->find('app:add-user');

        $this->assertEquals('app:add-user', $command->getName());
        $this->assertEquals(['app:create-user'], $command->getAliases());
        $this->assertEquals('Creates users and stores them in the database', $command->getDescription());
        $this->assertTrue($command->getDefinition()->hasArgument('username'));
        $this->assertTrue($command->getDefinition()->hasArgument('password'));
        $this->assertTrue($command->getDefinition()->hasOption('admin'));
    }

    public function testExecuteWithProvidedArguments(): void
    {
        $this->validator->expects($this->exactly(2))
            ->method('validatePropertyValue')
            ->willReturn(new ConstraintViolationList());

        $successResponse = new OperationResponseDTO(true, 'User test_user created successfully');

        $this->userService->expects($this->once())
            ->method('addUser')
            ->with('test_user', 'test_password', false)
            ->willReturn($successResponse);

        $this->commandTester->setInputs(['test_password']);

        $this->commandTester->execute([
                                          'username' => 'test_user',
                                          'password' => 'test_password',
                                      ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('User test_user created successfully', $output);
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithInteractiveInput(): void
    {
        $this->validator->expects($this->exactly(2))
            ->method('validatePropertyValue')
            ->willReturn(new ConstraintViolationList());

        $successResponse = new OperationResponseDTO(true, 'User interactive_user created successfully');

        $this->userService->expects($this->once())
            ->method('addUser')
            ->with('interactive_user', 'interactive_password', false)
            ->willReturn($successResponse);

        $this->commandTester->setInputs(['interactive_user', 'interactive_password', 'interactive_password']);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('User interactive_user created successfully', $output);
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithAdminOption(): void
    {
        $this->validator->expects($this->exactly(2))
            ->method('validatePropertyValue')
            ->willReturn(new ConstraintViolationList());

        $successResponse = new OperationResponseDTO(true, 'Admin user admin_user created successfully');

        $this->userService->expects($this->once())
            ->method('addUser')
            ->with('admin_user', 'admin_password', true)
            ->willReturn($successResponse);

        $this->commandTester->setInputs(['admin_password']);

        $this->commandTester->execute([
                                          'username' => 'admin_user',
                                          'password' => 'admin_password',
                                          '--admin' => true,
                                      ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Admin user admin_user created successfully', $output);
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithInvalidUsername(): void
    {
        $violation = $this->createMock(ConstraintViolation::class);
        $violation->method('getMessage')->willReturn('Username is too short');

        $violationList = new ConstraintViolationList([$violation]);

        $this->validator->expects($this->once())
            ->method('validatePropertyValue')
            ->with(User::class, 'username', 'u')
            ->willReturn($violationList);

        $this->userService->expects($this->never())->method('addUser');

        $this->commandTester->setInputs(['u']);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Error creating user', $output);
        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithInvalidPassword(): void
    {
        $violation = $this->createMock(ConstraintViolation::class);
        $violation->method('getMessage')->willReturn('Password is too weak');
        $violationList = new ConstraintViolationList([$violation]);

        $this->validator->expects($this->exactly(2))
            ->method('validatePropertyValue')
            ->willReturnOnConsecutiveCalls(
                new ConstraintViolationList(),
                $violationList
            );

        $this->userService->expects($this->never())->method('addUser');

        $this->commandTester->setInputs(['valid_user', 'weak', 'weak']);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Error creating user', $output);
        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithUserServiceFailure(): void
    {
        $this->validator->expects($this->exactly(2))
        ->method('validatePropertyValue')
            ->willReturn(new ConstraintViolationList());

        $failureResponse = new OperationResponseDTO(false, 'Username already exists');

        $this->userService->expects($this->once())
            ->method('addUser')
            ->with('existing_user', 'test_password', false)
            ->willReturn($failureResponse);

        $this->commandTester->setInputs(['test_password']);

        $this->commandTester->execute([
                                          'username' => 'existing_user',
                                          'password' => 'test_password',
                                      ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Error creating user', $output);
        $this->assertStringContainsString('Username already exists', $output);
        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
    }
}
