<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    $envFile = dirname(__DIR__).'/.env';
    if (is_file($envFile) && is_readable($envFile)) {
        (new Dotenv())->bootEnv($envFile);
    }
}
