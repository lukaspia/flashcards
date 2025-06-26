<?php

declare(strict_types=1);


namespace App\Service\Response;


/**
 *
 */
class OperationResponse
{
    /**
     * @param bool $success
     * @param string $message
     */
    public function __construct(private bool $success, private string $message)
    {
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     * @return void
     */
    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return void
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}