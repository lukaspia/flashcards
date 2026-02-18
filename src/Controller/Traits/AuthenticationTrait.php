<?php

declare(strict_types=1);

namespace App\Controller\Traits;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Trait providing authentication utilities for controllers
 */
trait AuthenticationTrait
{
    /**
     * @return \App\Entity\User
     */
    protected function requireAuthenticatedUser(): User
    {
        $user = $this->getUser();
        if (!$user) {
            if (property_exists($this, 'logger') && $this->logger) {
                $this->logger->warning('Unauthenticated access attempt', [
                    'controller' => static::class,
                    'route' => $this->getCurrentRouteName() ?? 'unknown'
                ]);
            }
            
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Authentication required.');
        }

        return $user;
    }

    /**
     * @return string|null
     */
    private function getCurrentRouteName(): ?string
    {
        try {
            $request = $this->getRequest ?? null;
            if ($request && method_exists($request, 'attributes')) {
                return $request->attributes->get('_route');
            }
        } catch (\Throwable $e) {
        }

        return null;
    }
}
