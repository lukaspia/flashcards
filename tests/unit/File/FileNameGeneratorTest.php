<?php

namespace App\Tests\File;

use App\File\FileNameGenerator;
use PHPUnit\Framework\TestCase;

class FileNameGeneratorTest extends TestCase
{
    private FileNameGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new FileNameGenerator();
    }

    /**
     * @dataProvider fileNameProvider
     */
    public function testGenerate(string $identifier, string $originalName, string $expectedExtension): void
    {
        $hash = md5($identifier);
        $expected = $expectedExtension ? $hash . '.' . $expectedExtension : $hash;

        $result = $this->generator->generate($identifier, $originalName);

        $this->assertEquals($expected, $result);
    }

    public function fileNameProvider(): array
    {
        return [
            'standard jpg' => ['user-1', 'photo.jpg', 'jpg'],
            'uppercase extension' => ['user-1', 'IMAGE.PNG', 'png'],
            'file with multiple dots' => ['lesson-123', 'my.document.pdf', 'pdf'],
            'file without extension' => ['id-99', 'README', ''],
            'extension with weird casing' => ['abc', 'test.JPEG', 'jpeg'],
        ];
    }

    public function testGenerateReturnsConsistentHash(): void
    {
        $identifier = 'test-id';
        $name = 'file.txt';

        $result1 = $this->generator->generate($identifier, $name);
        $result2 = $this->generator->generate($identifier, $name);

        $this->assertEquals($result1, $result2);
        $this->assertStringStartsWith(md5($identifier), $result1);
    }
}