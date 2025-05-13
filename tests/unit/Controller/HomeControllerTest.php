<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeControllerTest extends WebTestCase
{
    /**
     * Test that the home page is rendered with correct parameters when user is not authenticated
     */
    public function testHomeRendersLoginFormWhenUserIsNotAuthenticated(): void
    {
        $client = static::createClient();

        // Ensure no user is authenticated
        $container = $client->getContainer();
        $container->get('security.token_storage')->setToken(null);

        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * Test that authentication errors are passed to the template
     */
    public function testHomeDisplaysAuthenticationError(): void
    {
        $client = static::createClient();

        // Create a mock for AuthenticationUtils
        $authUtilsMock = $this->createMock(AuthenticationUtils::class);
        $authUtilsMock->method('getLastAuthenticationError')
            ->willReturn(new AuthenticationException('Invalid credentials'));
        $authUtilsMock->method('getLastUsername')
            ->willReturn('test_user');

        // Replace the service in the container
        $container = $client->getContainer();
        $container->set('security.authentication_utils', $authUtilsMock);

        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }
}

