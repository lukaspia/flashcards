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
    /**
     * @param \App\Service\User\UserService $userService
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     */
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
            ->addArgument(
                'username',
                InputArgument::OPTIONAL,
                'The username of the new user'
            )
            ->addArgument(
                'password',
                InputArgument::OPTIONAL,
                'The plain password of the new user'
            )
            ->addOption(
                'admin',
                null,
                InputOption::VALUE_NONE,
                'If set, the user is created as an administrator'
            );
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

        $isAdmin = $input->getOption('admin');

        try {
            $response = $this->userService->addUser($username, $password, (bool)$isAdmin);

            if ($response->isSuccess()) {
                $io->success($response->getMessage());
                return Command::SUCCESS;
            }

            $io->error(['Error creating user.', $response->getMessage()]);
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $io->error(['Error creating user.', $e->getMessage()]);
            return Command::FAILURE;
        }
    }

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
            $io->error(['Error creating user.', $errors]);
            return null;
        }

        return $username;
    }

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
            $io->error(['Error creating user.', $errors]);
            return null;
        }

        return $password;
    }
}
