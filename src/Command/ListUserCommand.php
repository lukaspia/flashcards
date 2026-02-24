<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:list-users',
    description: 'Show list of users',
    aliases: ['app:show-users']
)]
class ListUserCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setHelp('This command displays a table with all registered users.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('User List');

        $users = $this->userRepository->findAll();

        if (empty($users)) {
            $io->warning('No users found in the database.');
            return Command::SUCCESS;
        }

        $rows = array_map(fn(User $user) => [
            $user->getId(),
            $user->getUsername(),
            implode(', ', $user->getRoles()),
        ], $users);

        $io->table(
            ['ID', 'Username', 'Roles'],
            $rows
        );

        $io->note(sprintf('Total users: %d', count($users)));

        return Command::SUCCESS;
    }
}
