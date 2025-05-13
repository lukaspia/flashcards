<?php

namespace App\Command;

use App\Entity\User;
use App\Service\User\UserService;
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
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    private ValidatorInterface $validator;
    /**
     * @var \App\Service\UserService
     */
    private UserService $userService;

    /**
     * @param \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $passwordHasher
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     */
    public function __construct(UserService $userService, ValidatorInterface $validator)
    {
        parent::__construct();

        $this->validator = $validator;
        $this->userService = $userService;
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

        if (!($username = $input->getArgument('username'))) {
            $username = $io->ask('Username');
            $input->setArgument('username', $username);

            $errors = $this->validator->validatePropertyValue(User::class, 'username', $username);
            if (count($errors) > 0) {
                $io->error(['Error creating user.', $errors]);
                return Command::FAILURE;
            }
        }

        if (!($password = $input->getArgument('password'))) {
            $password = $io->askHidden('Password (your input will be hidden)');
            $input->setArgument('password', $password);

            $errors = $this->validator->validatePropertyValue(User::class, 'password', $password);
            if (count($errors) > 0) {
                $io->error(['Error creating user.', $errors]);
                return Command::FAILURE;
            }
        }

        $isAdmin = $input->getOption('admin');

        $response = $this->userService->addUser($username, $password, (bool)$isAdmin);

        if ($response->isSuccess()) {
            $io->success($response->getMessage());
            return Command::SUCCESS;
        }

        $io->error(['Error creating user.', $response->getMessage()]);
        return Command::FAILURE;
    }
}
