<?php

namespace App\Tests\Schema;

use App\Schema\TranslationSchema;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use PHPUnit\Framework\TestCase;

class TranslationSchemaTest extends TestCase
{
    public function testGetSchemaReturnsCorrectStructure(): void
    {
        $schema = TranslationSchema::getSchema();

        $this->assertArrayHasKey('translation', $schema);
        $this->assertArrayHasKey('example', $schema);

        $this->assertInstanceOf(Schema::class, $schema['translation']);
        $this->assertInstanceOf(Schema::class, $schema['example']);

        $this->assertEquals(DataType::STRING, $schema['translation']->type);
        $this->assertEquals(DataType::STRING, $schema['example']->type);
    }

    public function testGetSchemaCount(): void
    {
        $schema = TranslationSchema::getSchema();
        $this->assertCount(2, $schema, 'Schema should contain exactly 2 fields.');
    }
}
