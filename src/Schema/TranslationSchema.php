<?php

declare(strict_types=1);


namespace App\Schema;


use Gemini\Data\Schema;
use Gemini\Enums\DataType;

class TranslationSchema
{
    public static function getSchema(): array
    {
        return [
            'translation' => new Schema(type: DataType::STRING),
            'example' => new Schema(type: DataType::STRING)
        ];
    }
}