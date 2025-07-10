<?php

declare(strict_types=1);

namespace App\Tests\Schema;

use App\Schema\TranslationSchema;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use PHPUnit\Framework\TestCase;

class TranslationSchemaTest extends TestCase
{
    public function testGetSchemaReturnsCorrectStructure(): void
    {
        // When
        $schema = TranslationSchema::getSchema();

        // Then
        $this->assertIsArray($schema);
        $this->assertArrayHasKey('translation', $schema);
        $this->assertArrayHasKey('example', $schema);

        $this->assertInstanceOf(Schema::class, $schema['translation']);
        $this->assertInstanceOf(Schema::class, $schema['example']);

        $this->assertEquals(DataType::STRING, $schema['translation']->type);
        $this->assertEquals(DataType::STRING, $schema['example']->type);
    }
}
