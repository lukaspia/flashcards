<?php

declare(strict_types=1);

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Messenger\Exception\ValidationFailedException;

/**
 * Global API exception handler providing consistent JSON responses.
 */
readonly class ApiExceptionListener
{
    public function __construct(
        private LoggerInterface $logger,
        private string $environment
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        if (!$this->isApiRequest($request)) {
            return;
        }

        if ($exception instanceof ValidationFailedException) {
            $this->handleValidationException($event, $exception);
            return;
        }

        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;

        $this->logError($exception, $request, $statusCode);

        $responseData = [
            'status' => 'error',
            'data' => null,
            'message' => [$this->getPublicMessage($exception, $statusCode)],
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

    private function isApiRequest($request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api') ||
            $request->headers->get('Accept') === 'application/json';
    }

    private function handleValidationException(ExceptionEvent $event, ValidationFailedException $exception): void
    {
        $errors = [];
        foreach ($exception->getViolations() as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage()
            ];
        }

        $this->logger->warning('API Validation failed', [
            'errors' => $errors
        ]);

        $event->setResponse(new JsonResponse([
                                                 'status' => 'error',
                                                 'data' => null,
                                                 'errors' => $errors,
                                                 'message' => ['Validation failed']
                                             ], Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    private function logError(\Throwable $exception, $request, $statusCode): void
    {
        if ($statusCode === Response::HTTP_UNAUTHORIZED) {
            $this->logger->warning('Unauthenticated access attempt', [
                'path' => $request->getPathInfo(),
                'ip' => $request->getClientIp(),
            ]);
            return;
        }

        $this->logger->error('API Exception: ' . $exception->getMessage(), [
            'path' => $request->getPathInfo(),
            'status_code' => $statusCode,
            'exception' => $exception
        ]);
    }

    private function getPublicMessage(\Throwable $exception, int $statusCode): string
    {
        if ($exception instanceof HttpExceptionInterface && $statusCode < 500) {
            return $exception->getMessage();
        }

        return 'An unexpected error occurred. Please try again later.';
    }
}