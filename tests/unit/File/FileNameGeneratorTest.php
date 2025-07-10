<?php

namespace App\Tests\File;

use App\File\FileNameGenerator;
use PHPUnit\Framework\TestCase;

class FileNameGeneratorTest extends TestCase
{
    public function testGenerate()
    {
        $generator = new FileNameGenerator();
        $identifier = 'test-identifier';
        $originalFilename = 'document.pdf';
        $fileName = $generator->generate($identifier, $originalFilename);

        $expected = md5($identifier) . '.pdf';
        $this->assertEquals($expected, $fileName);
    }
}
