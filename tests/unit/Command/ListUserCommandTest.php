<?php

namespace App\Tests\Command;

use App\Command\ListUserCommand;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ListUserCommandTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $userRepository;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        // Create mocks
        $this->userRepository = $this->createMock(EntityRepository::class);

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entityManager
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($this->userRepository);

        // Create command
        $command = new ListUserCommand($this->entityManager);

        // Create application and add command
        $application = new Application();
        $application->add($command);

        // Create command tester
        $this->commandTester = new CommandTester($application->find('app:list-users'));
    }

    public function testCommandConfiguration(): void
    {
        $application = new Application();
        $command = new ListUserCommand($this->entityManager);
        $application->add($command);

        $command = $application->find('app:list-users');

        $this->assertEquals('app:list-users', $command->getName());
        $this->assertEquals('Show list of users', $command->getDescription());
        $this->assertContains('app:show-users', $command->getAliases());
        $this->assertEquals('User list:', $command->getHelp());
    }

    public function testExecuteWithNoUsers(): void
    {
        // Configure repository mock to return empty array
        $this->userRepository
            ->method('findAll')
            ->willReturn([]);

        // Execute command
        $this->commandTester->execute([]);

        // Get command output
        $output = $this->commandTester->getDisplay();

        // Assert command was successful
        $this->assertEquals(0, $this->commandTester->getStatusCode());

        // Assert output contains expected elements
        $this->assertStringContainsString('User list:', $output);
        $this->assertStringContainsString('ID', $output);
        $this->assertStringContainsString('Username', $output);
        $this->assertStringContainsString('Roles', $output);
        // Table should be empty (just headers)
        $this->assertStringNotContainsString('ROLE_', $output);
    }

    public function testExecuteWithUsers(): void
    {
        // Create mock users
        $user1 = $this->createMock(User::class);
        $user1->method('getId')->willReturn(1);
        $user1->method('getUsername')->willReturn('user1');
        $user1->method('getRoles')->willReturn(['ROLE_USER']);

        $user2 = $this->createMock(User::class);
        $user2->method('getId')->willReturn(2);
        $user2->method('getUsername')->willReturn('admin');
        $user2->method('getRoles')->willReturn(['ROLE_USER', 'ROLE_ADMIN']);

        // Configure repository mock to return users
        $this->userRepository
            ->method('findAll')
            ->willReturn([$user1, $user2]);

        // Execute command
        $this->commandTester->execute([]);

        // Get command output
        $output = $this->commandTester->getDisplay();

        // Assert command was successful
        $this->assertEquals(0, $this->commandTester->getStatusCode());

        // Assert output contains expected elements
        $this->assertStringContainsString('User list:', $output);
        $this->assertStringContainsString('1', $output);
        $this->assertStringContainsString('2', $output);
        $this->assertStringContainsString('user1', $output);
        $this->assertStringContainsString('admin', $output);
        $this->assertStringContainsString('ROLE_USER', $output);
        $this->assertStringContainsString('ROLE_ADMIN', $output);
    }

    public function testCommandAlias(): void
    {
        $application = new Application();
        $command = new ListUserCommand($this->entityManager);
        $application->add($command);

        // Test that the alias works
        $command = $application->find('app:show-users');

        $this->assertEquals('app:list-users', $command->getName());
    }
}
