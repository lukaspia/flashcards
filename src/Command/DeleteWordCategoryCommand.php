<?php

namespace App\Command;

use App\Service\Word\WordCategoryServiceInterface;
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
    public function __construct(
        private readonly WordCategoryServiceInterface $categoryService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you to delete a word category...')
            ->addArgument(
                'category_name',
                InputArgument::OPTIONAL,
                'The name of the category to delete'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info($this->getHelp());

        $name = $input->getArgument('category_name');

        if (!$name) {
            $name = $io->ask('Please enter the name of the category to delete');
        }

        if (empty($name)) {
            $io->error('Category name cannot be empty.');
            return Command::FAILURE;
        }

        try {
            $this->categoryService->deleteCategory($name);
            $io->success(sprintf('Category "%s" deleted successfully.', $name));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
