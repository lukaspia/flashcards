<?php

declare(strict_types=1);


namespace App\Service\User;


use App\DTO\OperationResponseDTO;
use App\Entity\User;
use App\Event\AddUserEvent;
use App\Event\RemoveUserEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly class UserService implements UserServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @param string $username
     * @param string $password
     * @param bool $isAdmin
     * @return \App\DTO\OperationResponseDTO
     */
    public function addUser(
        string $username,
        string $password,
        bool $isAdmin = false
    ): OperationResponseDTO {
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        if ($existingUser) {
            return new OperationResponseDTO(false, sprintf('Username "%s" is already in use', $username));
        }

        $user = new User();
        $user->setUsername($username);
        $user->setRoles([$isAdmin ? User::ROLE_ADMIN : User::ROLE_USER]);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            return new OperationResponseDTO(false, (string)$errors);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(
            new AddUserEvent($user),
            AddUserEvent::NAME
        );

        return new OperationResponseDTO(true, sprintf('New %s user successfully created.', $username));
    }

    /**
     * @param string $username
     * @return \App\DTO\OperationResponseDTO
     */
    public function deleteUser(string $username): OperationResponseDTO
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

        if (!$user) {
            return new OperationResponseDTO(false, sprintf('User %s not found.', $username));
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(
            new RemoveUserEvent($user),
            RemoveUserEvent::NAME
        );

        return new OperationResponseDTO(true, sprintf('User %s successfully deleted.', $username));
    }
}