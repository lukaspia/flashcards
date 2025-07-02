<?php

declare(strict_types=1);


namespace App\Service\User;


use App\DTO\OperationResponse;
use App\Entity\User;
use App\Event\AddUserEvent;
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
     * @return \App\DTO\OperationResponse
     */
    public function addUser(
        string $username,
        string $password,
        bool $isAdmin = false
    ): OperationResponse {
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        if ($existingUser) {
            return new OperationResponse(false, sprintf('Username "%s" is already in use', $username));
        }

        $user = new User();
        $user->setUsername($username);
        $user->setRoles([$isAdmin ? User::ROLE_ADMIN : User::ROLE_USER]);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            return new OperationResponse(false, (string)$errors);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $addUserEvent = new AddUserEvent($user);
        $this->eventDispatcher->dispatch($addUserEvent, AddUserEvent::NAME);

        return new OperationResponse(true, sprintf('New %s user successfully created.', $username));
    }

    /**
     * @param string $username
     * @return \App\DTO\OperationResponse
     */
    public function deleteUser(string $username): OperationResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        if (!$user) {
            return new OperationResponse(false, sprintf('User %s not found.', $username));
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new OperationResponse(true, sprintf('User %s successfully deleted.', $username));
    }
}