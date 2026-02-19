<?php

declare(strict_types=1);


namespace App\Service\Lesson;


use App\Service\AI\AIGeneratorInterface;
use App\Service\Lesson\LessonMessageProviderInterface;
use Psr\Log\LoggerInterface;

/**
 *
 */
readonly class AiLessonMessageProvider implements LessonMessageProviderInterface
{
    private const CONGRATS_PROMPT = "Generate one, short, encouraging message just in Polish (don't give me translation in English), to congratulate someone on their successful foreign language vocabulary learning.";
    private const CONGRATS_DEFAULT_MESSAGE = "Świetna robota! Twoje słownictwo staje się coraz lepsze.";

    /**
     * @param \App\Service\AI\AIGeneratorInterface $aiGenerator
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        private AIGeneratorInterface $aiGenerator,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @return string
     */
    public function getCongratsMessage(): string
    {
        try {
            return $this->aiGenerator->generateText(self::CONGRATS_PROMPT);
        } catch (\Exception $e) {
            $this->logger->warning('AI Message generation failed, using fallback', [
                'error' => $e->getMessage(),
                'exception' => get_class($e)
            ]);

            return self::CONGRATS_DEFAULT_MESSAGE;
        }
    }
}