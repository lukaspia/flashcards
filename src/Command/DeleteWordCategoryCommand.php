<?php

namespace App\Command;

use App\Entity\WordCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:delete-word-category',
    description: 'Delete word category from database',
    aliases: ['app:remove-word-category']
)]
class DeleteWordCategoryCommand extends Command
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @param \App\Service\UserService $userService
     */
    public function __construct(EntityManagerInterface $entityManager)
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
            ->setHelp('This command allows you to delete a word category...')
            ->addArgument('category_name', InputArgument::OPTIONAL, 'The category name of the new word category');
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

        if (!($wordCategoryName = $input->getArgument('category_name'))) {
            $wordCategoryName = $io->ask('Word category name');
            $input->setArgument('category_name', $wordCategoryName);
        }

        $wordCategory = $this->entityManager->getRepository(WordCategory::class)->findBy(['name' => $wordCategoryName]);

        if(empty($wordCategory)) {
            $io->error(['Category not found.']);
            return Command::FAILURE;
        }

        $this->entityManager->remove(reset($wordCategory));
        $this->entityManager->flush();

        $io->success(['Category deleted successfully']);
        return Command::SUCCESS;
    }
}
