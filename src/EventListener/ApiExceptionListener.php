<?php

declare(strict_types=1);


namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 *
 */
readonly class ApiExceptionListener
{
    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $environment
     */
    public function __construct(
        private LoggerInterface $logger,
        private string $environment
    ) {
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     * @return void
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        if (!$this->isApiRequest($request)) {
            return;
        }

        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;

        $this->logError($exception, $request);

        $responseData = [
            'data' => null,
            'errors' => [$this->getPublicMessage($exception, $statusCode)],
            'status' => 'error'
        ];

        if ($this->environment === 'dev') {
            $responseData['debug'] = [
                'message' => $exception->getMessage(),
                'class' => get_class($exception),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        $event->setResponse(new JsonResponse($responseData, $statusCode));
    }

    /**
     * @param $request
     * @return bool
     */
    private function isApiRequest($request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api') ||
            $request->headers->get('Accept') === 'application/json';
    }

    /**
     * @param \Throwable $exception
     * @param $request
     * @return void
     */
    private function logError(\Throwable $exception, $request): void
    {
        $this->logger->error('API Exception: ' . $exception->getMessage(), [
            'path' => $request->getPathInfo(),
            'exception' => $exception
        ]);
    }

    /**
     * @param \Throwable $exception
     * @param int $statusCode
     * @return string
     */
    private function getPublicMessage(\Throwable $exception, int $statusCode): string
    {
        if ($exception instanceof HttpExceptionInterface && $statusCode < 500) {
            return $exception->getMessage();
        }

        return 'An unexpected error occurred. Please try again later.';
    }
}