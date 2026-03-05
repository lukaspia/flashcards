<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Word\WordCategoryServiceInterface;
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
    public function __construct(
        private readonly WordCategoryServiceInterface $categoryService
    ) {
        parent::__construct();
    }

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info($this->getHelp());

        $name = $input->getArgument('category_name');

        if (!$name) {
            $name = $io->ask('Please enter the word category name');
        }

        if (empty(trim((string)$name))) {
            $io->error('Category name cannot be empty.');
            return Command::FAILURE;
        }

        try {
            $category = $this->categoryService->createCategory($name);
            $io->success(
                sprintf('Category "%s" (ID: %d) created successfully', $category->getName(), $category->getId())
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
