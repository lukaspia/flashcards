<?php

namespace App\Command;

use App\Service\User\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:delete-user',
    description: 'Delete users from database',
    aliases: ['app:remove-user']
)]
class DeleteUserCommand extends Command
{
    /**
     * @var \App\Service\UserService
     */
    private UserService $userService;

    /**
     * @param \App\Service\UserService $userService
     */
    public function __construct(UserService $userService)
    {
        parent::__construct();

        $this->userService = $userService;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you to delete a user...')
            ->addArgument('username', InputArgument::OPTIONAL, 'The username of the new user');
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
        }

        $response = $this->userService->deleteUser($username);

        if ($response->isSuccess()) {
            $io->success($response->getMessage());
            return Command::SUCCESS;
        }

        $io->error(['Error deleting user.', $response->getMessage()]);
        return Command::FAILURE;
    }
}
