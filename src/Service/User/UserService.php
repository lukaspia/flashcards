<?php

declare(strict_types=1);


namespace App\Service\User;


use App\Entity\User;
use App\Event\AddUserEvent;
use App\Service\Response\OperationResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserService
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $passwordHasher;
    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    private ValidatorInterface $validator;
    /**
     * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
     */
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string $username
     * @param string $password
     * @param bool $isAdmin
     * @return \App\Service\Response\OperationResponse
     */
    public function addUser(
        string $username,
        string $password,
        bool $isAdmin = false
    ): OperationResponse {
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
     * @return \App\Service\Response\OperationResponse
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