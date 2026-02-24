<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Service\User\UserServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:add-user',
    description: 'Creates users and stores them in the database',
    aliases: ['app:create-user']
)]
class AddUserCommand extends Command
{
    public function __construct(
        private readonly UserServiceInterface $userService,
        private readonly ValidatorInterface $validator
    ) {
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you to create a user...')
            ->addArgument('username', InputArgument::OPTIONAL, 'The username of the new user')
            ->addArgument('password', InputArgument::OPTIONAL, 'The plain password of the new user')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'If set, the user is created as an administrator');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info($this->getHelp());

        $username = $this->getUsername($input, $io);
        if ($username === null) {
            return Command::FAILURE;
        }

        $password = $this->getPassword($input, $io);
        if ($password === null) {
            return Command::FAILURE;
        }

        $isAdmin = (bool)$input->getOption('admin');

        try {
            $user = $this->userService->addUser($username, $password, $isAdmin);

            $io->success(sprintf('New %s user successfully created (ID: %d).', $user->getUsername(), $user->getId()));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(['Error creating user.', $e->getMessage()]);

            return Command::FAILURE;
        }
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     * @return string|null
     */
    private function getUsername(InputInterface $input, SymfonyStyle $io): ?string
    {
        $username = trim((string)$input->getArgument('username') ?: '');

        while (empty($username)) {
            $username = trim((string)$io->ask('Please enter the username'));
            if (empty($username)) {
                $io->warning('Username cannot be empty');
            }
        }

        $errors = $this->validator->validatePropertyValue(User::class, 'username', $username);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $io->error($error->getMessage());
            }
            return null;
        }

        return $username;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     * @return string|null
     */
    private function getPassword(InputInterface $input, SymfonyStyle $io): ?string
    {
        $password = $input->getArgument('password');

        if (empty($password)) {
            $password = $io->askHidden('Password (your input will be hidden)');
            if ($password === null) {
                return null;
            }
        }

        $confirmPassword = $io->askHidden('Please confirm the password');

        if ($password !== $confirmPassword) {
            $io->error('Passwords do not match');
            return null;
        }

        $errors = $this->validator->validatePropertyValue(User::class, 'password', $password);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $io->error($error->getMessage());
            }
            return null;
        }

        return $password;
    }
}
