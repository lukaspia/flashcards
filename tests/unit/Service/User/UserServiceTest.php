<?php

namespace App\Tests\Service\User;

use App\Entity\User;
use App\Event\AddUserEvent;
use App\Repository\UserRepository;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserServiceTest extends TestCase
{
    private MockObject|EntityManagerInterface $entityManager;
    private MockObject|UserRepository $userRepository;
    private MockObject|UserPasswordHasherInterface $passwordHasher;
    private MockObject|ValidatorInterface $validator;
    private MockObject|EventDispatcherInterface $eventDispatcher;
    private UserService $service;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->service = new UserService(
            $this->entityManager,
            $this->userRepository,
            $this->passwordHasher,
            $this->validator,
            $this->eventDispatcher
        );
    }

    public function testAddUserSuccess(): void
    {
        $username = 'lukasz_dev';
        $plainPassword = 'secret_password';
        $hashedPassword = 'hashed_system_password_123';

        $this->userRepository->method('findOneBy')->willReturn(null);

        $this->passwordHasher->method('hashPassword')
            ->willReturn($hashedPassword);

        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $violations->method('count')->willReturn(0);
        $this->validator->method('validate')->willReturn($violations);

        $this->entityManager->expects($this->once())->method('persist')->with($this->isInstanceOf(User::class));
        $this->entityManager->expects($this->once())->method('flush');

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(AddUserEvent::class), AddUserEvent::NAME);

        $user = $this->service->addUser($username, $plainPassword, true);

        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($hashedPassword, $user->getPassword());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }

    public function testAddUserThrowsExceptionIfUsernameTaken(): void
    {
        $this->userRepository->method('findOneBy')->willReturn(new User());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('already in use');

        $this->service->addUser('existing_user', 'password');
    }

    public function testDeleteUserSuccess(): void
    {
        $user = new User();
        $this->userRepository->method('findOneBy')->willReturn($user);

        $this->entityManager->expects($this->once())->method('remove')->with($user);
        $this->entityManager->expects($this->once())->method('flush');
        $this->eventDispatcher->expects($this->once())->method('dispatch');

        $this->service->deleteUser('user_to_delete');
    }
}