<?php

namespace App\Tests\Command;

use App\Command\ListUserCommand;
use App\Entity\User;
use App\Repository\UserRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ListUserCommandTest extends TestCase
{
    private MockObject|UserRepository $userRepository;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $command = new ListUserCommand($this->userRepository);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteShowsTableWithUsers(): void
    {
        $user1 = $this->createMock(User::class);
        $user1->method('getId')->willReturn(1);
        $user1->method('getUsername')->willReturn('lukasz_admin');
        $user1->method('getRoles')->willReturn(['ROLE_USER', 'ROLE_ADMIN']);

        $user2 = $this->createMock(User::class);
        $user2->method('getId')->willReturn(2);
        $user2->method('getUsername')->willReturn('testowy_user');
        $user2->method('getRoles')->willReturn(['ROLE_USER']);

        $this->userRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$user1, $user2]);

        $result = $this->commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $result);
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('User List', $output);
        $this->assertStringContainsString('lukasz_admin', $output);
        $this->assertStringContainsString('ROLE_ADMIN', $output);
        $this->assertStringContainsString('testowy_user', $output);
        $this->assertStringContainsString('Total users: 2', $output);
    }

    public function testExecuteShowsWarningWhenNoUsersFound(): void
    {
        $this->userRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $result = $this->commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $result);
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('No users found in the database.', $output);

        $this->assertStringNotContainsString('Total users:', $output);
    }
}