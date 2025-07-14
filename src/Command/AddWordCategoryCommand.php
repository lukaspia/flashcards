<?php

declare(strict_types=1);

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
    name: 'app:add-word-category',
    description: 'Creates word category and stores it in the database'
)]
class AddWordCategoryCommand extends Command
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you to create a word category...')
            ->addArgument(
                'category_name',
                InputArgument::OPTIONAL,
                'The name of the new word category'
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

        if (!($wordCategoryName = $this->getCategoryName($input, $io))) {
            return Command::FAILURE;
        }

        $wordCategory = new WordCategory();
        $wordCategory->setName($wordCategoryName);

        $this->entityManager->persist($wordCategory);
        $this->entityManager->flush();

        $io->success('Category created successfully');
        return Command::SUCCESS;
    }

    private function getCategoryName(InputInterface $input, SymfonyStyle $io): ?string
    {
        $categoryName = trim((string)$input->getArgument('category_name') ?: '');

        if (!$categoryName) {
            $categoryName = trim((string)$io->ask('Word category name'));
            $input->setArgument('category_name', $categoryName);

            if ($categoryName === '') {
                $io->error('Error: Category name cannot be empty.');
                return null;
            }
        }

        return $categoryName;
    }
}
