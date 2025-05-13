<?php

namespace App\Command;

use App\Entity\User;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface as EntityManagerInterfaceAlias;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:list-users',
    description: 'Show list of users',
    aliases: ['app:show-users']
)]
class ListUserCommand extends Command
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterfaceAlias $entityManager;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterfaceAlias $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setHelp('User list:');
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

        $users = $this->entityManager->getRepository(User::class)->findAll();

        $usersTable = [];
        if(!empty($users)) {
            foreach ($users as $user) {
                $usersTable[] = [
                    $user->getId(),
                    $user->getUsername(),
                    implode(', ', $user->getRoles()),
                ];
            }
        }

        $io->table(
            ['ID', 'Username', 'Roles'],
            $usersTable
        );

        return Command::SUCCESS;
    }
}
