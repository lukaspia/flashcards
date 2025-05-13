<?php

namespace App\Tests\Command;

use App\Command\AddUserCommand;
use App\Entity\User;
use App\Service\Response\OperationResponse;
use App\Service\User\UserService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddUserCommandTest extends TestCase
{
    private UserService $userService;
    private ValidatorInterface $validator;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserService::class);
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
        // Configure validator to return no errors
        $this->validator->expects($this->never())
            ->method('validatePropertyValue');

        // Configure user service to return success
        $successResponse = new OperationResponse(true, 'User test_user created successfully');

        $this->userService->expects($this->once())
            ->method('addUser')
            ->with('test_user', 'test_password', false)
            ->willReturn($successResponse);

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
        // Configure validator to return no errors
        $this->validator->expects($this->exactly(2))
            ->method('validatePropertyValue')
            ->willReturn(new ConstraintViolationList());

        // Configure user service to return success
        $successResponse = new OperationResponse(true, 'User interactive_user created successfully');

        $this->userService->expects($this->once())
            ->method('addUser')
            ->with('interactive_user', 'interactive_password', false)
            ->willReturn($successResponse);

        $this->commandTester->setInputs(['interactive_user', 'interactive_password']);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('User interactive_user created successfully', $output);
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithAdminOption(): void
    {
        // Configure validator to return no errors
        $this->validator->expects($this->never())
            ->method('validatePropertyValue');

        // Configure user service to return success
        $successResponse = new OperationResponse(true, 'Admin user admin_user created successfully');

        $this->userService->expects($this->once())
            ->method('addUser')
            ->with('admin_user', 'admin_password', true)
            ->willReturn($successResponse);

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
        // Create a constraint violation for username
        $violation = $this->createMock(ConstraintViolation::class);
        $violation->method('getMessage')->willReturn('Username is too short');

        $violationList = new ConstraintViolationList([$violation]);

        // Configure validator to return errors for username
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
        // Create a constraint violation for password
        $violation = $this->createMock(ConstraintViolation::class);
        $violation->method('getMessage')->willReturn('Password is too weak');
        $violationList = new ConstraintViolationList([$violation]);

        // Configure validator with consecutive calls
        $this->validator->expects($this->exactly(2))
            ->method('validatePropertyValue')
            ->willReturnOnConsecutiveCalls(
                new ConstraintViolationList(), // First call returns empty list (valid username)
                $violationList                 // Second call returns violations (invalid password)
            );

        $this->userService->expects($this->never())->method('addUser');

        $this->commandTester->setInputs(['valid_user', 'weak']);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Error creating user', $output);
        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithUserServiceFailure(): void
    {
        // Configure validator to return no errors
        $this->validator->expects($this->never())
            ->method('validatePropertyValue');

        // Configure user service to return failure
        $failureResponse = new OperationResponse(false, 'Username already exists');

        $this->userService->expects($this->once())
            ->method('addUser')
            ->with('existing_user', 'test_password', false)
            ->willReturn($failureResponse);

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

