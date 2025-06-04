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
     * @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        parent::__construct();

        $this->parameterBag = $parameterBag;
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command deletes temporary files older than a specified duration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info('Starting temporary upload cleanup...');

        $threshold = (new \DateTimeImmutable())->modify('-' . self::THRESHOLD . ' hours');

        $cleanedCount = 0;

        $tempDirectory = $this->parameterBag->get('word_image_upload_dir_temp');

        if (!is_dir($tempDirectory)) {
            return Command::FAILURE;
        }

        $files = scandir($tempDirectory);

        foreach ($files as $file) {
            if (!in_array($file, array(".", ".."))) {
                if ($this->isFileCreatedBeforeDate($tempDirectory . '/' . $file, $threshold->format('Y-m-d H:i:s'))) {
                    unlink($tempDirectory . '/' . $file);
                    $cleanedCount++;
                    $io->success('File: ' . $file . ' was deleted.');
                }
            }
        }

        $io->success('Deleted files: ' . $cleanedCount);

        return Command::SUCCESS;
    }

    function isFileCreatedBeforeDate(string $filePath, string $targetDateString): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $fileCreationTime = filectime($filePath);

        $targetTimestamp = strtotime($targetDateString);

        if ($targetTimestamp === false) {
            return false;
        }

        return $fileCreationTime < $targetTimestamp;
    }
}
