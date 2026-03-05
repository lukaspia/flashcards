<?php

declare(strict_types=1);

namespace App\Command;

use App\File\FileManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Can be run as a cron job to clean up temporary files older than a specified duration.
 */
#[AsCommand(
    name: 'app:cleanup-temp',
    description: 'Cleans up temporary file uploads older than a specified duration.'
)]
class CleanupTemporaryUploadsCommand extends Command
{
    private const THRESHOLD_HOURS = 5;

    public function __construct(
        private readonly FileManagerInterface $fileManager,
        private readonly string $uploadDirTemp
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info(sprintf('Starting cleanup in: %s', $this->uploadDirTemp));

        try {
            $deletedCount = $this->fileManager->cleanupOldFiles(
                $this->uploadDirTemp,
                self::THRESHOLD_HOURS
            );

            $io->success(sprintf('Cleanup finished. Deleted %d files.', $deletedCount));
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Cleanup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
