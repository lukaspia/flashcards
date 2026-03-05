<?php

declare(strict_types=1);

namespace App\Service\AI;

use Gemini\Client;

/**
 *
 */
final class GeminiSdkClient implements GeminiClientInterface
{
    /**
     * @param \Gemini\Client $client
     */
    public function __construct(private Client $client)
    {
    }

    /**
     * @param string $model
     * @return \App\Service\AI\GeminiModelInterface
     */
    public function generativeModel(string $model): GeminiModelInterface
    {
        $sdkModel = $this->client->generativeModel(model: $model);

        return new GeminiSdkModel($sdkModel);
    }
}
