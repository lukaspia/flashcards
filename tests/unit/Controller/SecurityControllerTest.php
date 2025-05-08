<?php

namespace App\Tests\Controller;

use App\Controller\SecurityController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Attribute\Route;
use ReflectionClass;

class SecurityControllerTest extends TestCase
{
    private SecurityController $securityController;

    protected function setUp(): void
    {
        $this->securityController = new SecurityController();
    }

    public function testLogoutThrowsLogicException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This method can be blank - it will be intercepted by the logout key on your firewall.');

        $this->securityController->logout();
    }

    public function testLogoutRouteIsConfiguredCorrectly(): void
    {
        $reflectionClass = new ReflectionClass(SecurityController::class);
        $method = $reflectionClass->getMethod('logout');
        $attributes = $method->getAttributes(Route::class);

        $this->assertCount(1, $attributes, 'The logout method should have exactly one Route attribute');

        $routeAttribute = $attributes[0]->newInstance();
        $this->assertEquals('/logout', $routeAttribute->getPath(), 'The path should be "/logout"');
        $this->assertEquals('app_logout', $routeAttribute->getName(), 'The route name should be "app_logout"');
    }
}

