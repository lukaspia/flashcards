<?php

declare(strict_types=1);

namespace App\Service\AI;

interface GeminiClientInterface
{
    public function generativeModel(string $model): GeminiModelInterface;
}
