<?php

namespace App\Tests\Service\User;

use App\Entity\User;
use App\Event\AddUserEvent;
use App\Service\Response\OperationResponse;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private ValidatorInterface $validator;
    private EventDispatcherInterface $eventDispatcher;
    private UserService $userService;
    private EntityRepository $userRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->userRepository = $this->createMock(EntityRepository::class);

        $this->entityManager->method('getRepository')
            ->with(User::class)
            ->willReturn($this->userRepository);

        $this->userService = new UserService(
            $this->entityManager,
            $this->passwordHasher,
            $this->validator,
            $this->eventDispatcher
        );
    }

    public function testAddUserSuccessfully(): void
    {
        // Arrange
        $username = 'testuser';
        $password = 'password123';
        $hashedPassword = 'hashed_password';

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->willReturn($hashedPassword);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (User $user) use ($username, $hashedPassword) {
                return $user->getUsername() === $username &&
                    $user->getPassword() === $hashedPassword &&
                    in_array(User::ROLE_USER, $user->getRoles());
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(AddUserEvent::class),
                AddUserEvent::NAME
            );

        // Act
        $response = $this->userService->addUser($username, $password);

        // Assert
        $this->assertTrue($response->isSuccess());
        $this->assertEquals(sprintf('New %s user successfully created.', $username), $response->getMessage());
    }

    public function testAddAdminUserSuccessfully(): void
    {
        // Arrange
        $username = 'admin';
        $password = 'admin123';
        $hashedPassword = 'hashed_admin_password';

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->willReturn($hashedPassword);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (User $user) use ($username, $hashedPassword) {
                return $user->getUsername() === $username &&
                    $user->getPassword() === $hashedPassword &&
                    in_array(User::ROLE_ADMIN, $user->getRoles());
            }));

        // Act
        $response = $this->userService->addUser($username, $password, true);

        // Assert
        $this->assertTrue($response->isSuccess());
    }

    public function testAddUserWithValidationErrors(): void
    {
        // Arrange
        $username = 'invalid';
        $password = 'short';
        $errorMessage = 'Username is too short';

        $violationList = new ConstraintViolationList([
                                                         new ConstraintViolation($errorMessage, null, [], null, 'username', null)
                                                     ]);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($violationList);

        $this->entityManager->expects($this->never())
            ->method('persist');

        $this->entityManager->expects($this->never())
            ->method('flush');

        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        // Act
        $response = $this->userService->addUser($username, $password);

        // Assert
        $this->assertFalse($response->isSuccess());
        $this->assertStringContainsString($errorMessage, $response->getMessage());
    }

    public function testDeleteUserSuccessfully(): void
    {
        // Arrange
        $username = 'userToDelete';
        $user = new User();
        $user->setUsername($username);

        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['username' => $username])
            ->willReturn($user);

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($user);

        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $response = $this->userService->deleteUser($username);

        // Assert
        $this->assertTrue($response->isSuccess());
        $this->assertEquals(sprintf('User %s successfully deleted.', $username), $response->getMessage());
    }

    public function testDeleteNonExistentUser(): void
    {
        // Arrange
        $username = 'nonExistentUser';

        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['username' => $username])
            ->willReturn(null);

        $this->entityManager->expects($this->never())
            ->method('remove');

        $this->entityManager->expects($this->never())
            ->method('flush');

        // Act
        $response = $this->userService->deleteUser($username);

        // Assert
        $this->assertFalse($response->isSuccess());
        $this->assertEquals(sprintf('User %s not found.', $username), $response->getMessage());
    }
}

