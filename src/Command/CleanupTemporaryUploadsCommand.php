<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Can be run as a cron job to clean up temporary files older than a specified duration.
 */
#[AsCommand(
    name: 'app:cleanup-temp',
    description: 'Cleans up temporary file uploads older than a specified duration.'
)]
class CleanupTemporaryUploadsCommand extends Command
{
    private const THRESHOLD = 5;

    /**
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     */
    public function __construct(private readonly ParameterBagInterface $parameterBag)
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setHelp('This command deletes temporary files older than a specified duration.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info('Starting temporary upload cleanup...');

        $tempDirectory = $this->parameterBag->get('word_image_upload_dir_temp');
        if (!is_dir($tempDirectory)) {
            return Command::FAILURE;
        }

        $files = scandir($tempDirectory);
        $threshold = (new \DateTimeImmutable())->modify('-' . self::THRESHOLD . ' hours');
        $deletedFiles = 0;

        foreach ($files as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            $filePath = $tempDirectory . '/' . $file;

            try {
                if ($this->isFileCreatedBeforeDate($filePath, $threshold->format('Y-m-d H:i:s'))) {
                    if (@unlink($filePath)) {
                        $deletedFiles++;
                        $io->success(sprintf('Successfully deleted file: %s', $filePath));
                    } else {
                        $io->warning(sprintf('Failed to delete file (permission issue?): %s', $filePath));
                    }
                }
            } catch (\Exception $e) {
                $io->error(sprintf('Error processing file %s: %s', $filePath, $e->getMessage()));
            }
        }

        $io->success('Deleted files: ' . $deletedFiles);

        return Command::SUCCESS;
    }

    /**
     * @param string $filePath
     * @param string $targetDateString
     * @return bool
     */
    private function isFileCreatedBeforeDate(string $filePath, string $targetDateString): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        // Parse the target date string in UTC
        $utc = new \DateTimeZone('UTC');
        $targetDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $targetDateString, $utc);
        if ($targetDateTime === false) {
            return false;
        }
        $targetTimestamp = $targetDateTime->getTimestamp();

        $fileCreationTime = filemtime($filePath);

        return $fileCreationTime < $targetTimestamp;
    }
}
