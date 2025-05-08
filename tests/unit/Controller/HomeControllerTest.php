<?php

namespace App\Tests\Controller;

use App\Controller\HomeController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeControllerTest extends WebTestCase
{
    /**
     * Test that authenticated users are redirected to the panel
     */
    /*public function testHomeRedirectsWhenUserIsAuthenticated(): void
    {
        $client = static::createClient();

        // Mock authentication to simulate logged in user
        $userMock = $this->createMock(UserInterface::class);
        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->method('getUser')->willReturn($userMock);

        $container = $client->getContainer();
        $container->get('security.token_storage')->setToken($tokenMock);

        $client->request('GET', '/');

        $this->assertResponseRedirects('/panel'); // Assuming 'app_panel' route maps to /panel
    }*/

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
        $this->assertSelectorTextContains('.alert-danger', 'An authentication exception occurred.'); // Assuming error is displayed in an element with class alert-danger
    }

    /**
     * Test the controller directly with mocked dependencies
     */
    /*public function testHomeControllerDirectly(): void
    {
        $controller = new HomeController();

        // Mock AuthenticationUtils
        $authUtilsMock = $this->createMock(AuthenticationUtils::class);
        $authUtilsMock->method('getLastAuthenticationError')->willReturn(null);
        $authUtilsMock->method('getLastUsername')->willReturn('test_user');

        // Create a partial mock of the controller to mock getUser and render methods
        $controllerMock = $this->getMockBuilder(HomeController::class)
            ->onlyMethods(['getUser', 'render', 'redirectToRoute'])
            ->getMock();

        // Test case: User is not authenticated
        $controllerMock->method('getUser')->willReturn(null);
        $controllerMock->expects($this->once())
            ->method('render')
            ->with(
                'pages/home.html.twig',
                [
                    'last_username' => 'test_user',
                    'error' => null,
                ]
            )
            ->willReturn(new Response());

        $controllerMock->home($authUtilsMock);

        // Test case: User is authenticated
        $controllerMock = $this->getMockBuilder(HomeController::class)
            ->onlyMethods(['getUser', 'redirectToRoute'])
            ->getMock();

        $userMock = $this->createMock(UserInterface::class);
        $controllerMock->method('getUser')->willReturn($userMock);
        $controllerMock->expects($this->once())
            ->method('redirectToRoute')
            ->with('app_panel')
            ->willReturn(new Response());

        $controllerMock->home($authUtilsMock);
    }*/
}

