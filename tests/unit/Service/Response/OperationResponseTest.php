<?php

declare(strict_types=1);

namespace App\Tests\Service\Response;

use App\Service\Response\OperationResponse;
use PHPUnit\Framework\TestCase;

class OperationResponseTest extends TestCase
{
    public function testConstructor(): void
    {
        $response = new OperationResponse(true, 'Operation successful');

        $this->assertTrue($response->isSuccess());
        $this->assertEquals('Operation successful', $response->getMessage());
    }

    public function testIsSuccess(): void
    {
        $response = new OperationResponse(true, 'Success message');
        $this->assertTrue($response->isSuccess());

        $response = new OperationResponse(false, 'Error message');
        $this->assertFalse($response->isSuccess());
    }

    public function testGetMessage(): void
    {
        $message = 'Test message';
        $response = new OperationResponse(true, $message);

        $this->assertEquals($message, $response->getMessage());
    }

    public function testSetSuccess(): void
    {
        $response = new OperationResponse(true, 'Initial message');
        $this->assertTrue($response->isSuccess());

        $response->setSuccess(false);
        $this->assertFalse($response->isSuccess());

        $response->setSuccess(true);
        $this->assertTrue($response->isSuccess());
    }

    public function testSetMessage(): void
    {
        $initialMessage = 'Initial message';
        $newMessage = 'New message';

        $response = new OperationResponse(true, $initialMessage);
        $this->assertEquals($initialMessage, $response->getMessage());

        $response->setMessage($newMessage);
        $this->assertEquals($newMessage, $response->getMessage());
    }

    public function testWithEmptyMessage(): void
    {
        $response = new OperationResponse(false, '');

        $this->assertFalse($response->isSuccess());
        $this->assertEquals('', $response->getMessage());
    }

    public function testWithLongMessage(): void
    {
        $longMessage = str_repeat('a', 1000);
        $response = new OperationResponse(true, $longMessage);

        $this->assertTrue($response->isSuccess());
        $this->assertEquals($longMessage, $response->getMessage());
    }

    public function testSuccessStateToggle(): void
    {
        $response = new OperationResponse(true, 'Success');
        $this->assertTrue($response->isSuccess());

        $response->setSuccess(false);
        $this->assertFalse($response->isSuccess());

        $response->setSuccess(true);
        $this->assertTrue($response->isSuccess());
    }
}

