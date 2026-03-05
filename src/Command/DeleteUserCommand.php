<?php

namespace App\Command;

use App\Service\User\UserServiceInterface;
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
    public function __construct(private readonly UserServiceInterface $userService)
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you to delete a user...')
            ->addArgument(
                'username',
                InputArgument::OPTIONAL,
                'The username of the user to delete'
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

        $username = $input->getArgument('username');

        if (!$username) {
            $username = $io->ask('Please enter the username of the user to delete');
        }

        if (empty($username)) {
            $io->error('Username cannot be empty.');
            return Command::FAILURE;
        }

        try {
            $this->userService->deleteUser($username);

            $io->success(sprintf('User "%s" has been successfully deleted.', $username));
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error([
                           'Error deleting user.',
                           $e->getMessage()
                       ]);
            return Command::FAILURE;
        }
    }
}
