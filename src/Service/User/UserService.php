<?php

declare(strict_types=1);


namespace App\Service\User;


use App\Entity\User;
use App\Event\AddUserEvent;
use App\Event\RemoveUserEvent;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly class UserService implements UserServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @param string $username
     * @param string $password
     * @param bool $isAdmin
     * @return User
     * @throws \InvalidArgumentException
     */
    public function addUser(string $username, string $password, bool $isAdmin = false): User
    {
        if ($this->userRepository->findOneBy(['username' => $username])) {
            throw new \InvalidArgumentException(sprintf('Username "%s" is already in use', $username));
        }

        $user = new User();
        $user->setUsername($username);

        $user->setRoles([$isAdmin ? User::ROLE_ADMIN : User::ROLE_USER]);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->validate($user);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // 6. Eventy
        $this->eventDispatcher->dispatch(
            new AddUserEvent($user),
            AddUserEvent::NAME
        );

        return $user;
    }

    /**
     * @param string $username
     * @return void
     * @throws \InvalidArgumentException
     */
    public function deleteUser(string $username): void
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user) {
            throw new \InvalidArgumentException(sprintf('User %s not found.', $username));
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(
            new RemoveUserEvent($user),
            RemoveUserEvent::NAME
        );
    }

    /**
     * @param User $user
     * @return void
     * @throws \InvalidArgumentException
     */
    private function validate(User $user): void
    {
        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string) $errors);
        }
    }
}