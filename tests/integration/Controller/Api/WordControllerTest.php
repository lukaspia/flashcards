<?php

declare(strict_types=1);


namespace App\Tests\integration\Controller\Api;


use App\Entity\User;
use App\Service\Word\WordTranslationServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class WordControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();

        if (!$this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->getConnection()->beginTransaction();
        }
    }

    protected function tearDown(): void
    {
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->getConnection()->rollBack();
        }

        parent::tearDown();
        $this->entityManager->close();
    }

    private function loginAsTestUser(): User
    {
        $user = new User();

        $user->setUsername('test_user_' . bin2hex(random_bytes(2)));
        $user->setRoles([User::ROLE_USER]);
        $user->setPassword('password123');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->loginUser($user);

        return $user;
    }

    public function testTranslateRequiresAuthentication(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/words/translate',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            json_encode([
                            'word' => 'test',
                            'sourceLanguage' => 'pl-PL',
                            'targetLanguage' => 'en-US'
                        ])
        );

        $response = $this->client->getResponse();

        $this->assertEquals(302, $response->getStatusCode(), 'Expected redirect to login page.');

        $location = $response->headers->get('location');
        $this->assertStringContainsString('/', $location, 'Response should redirect to the home/login page.');
    }

    public function testTranslateSuccess(): void
    {
        $this->loginAsTestUser();

        $translationServiceMock = $this->createMock(WordTranslationServiceInterface::class);
        $translationServiceMock->expects($this->once())
            ->method('translate')
            ->willReturn(['translation' => 'developer']);

        static::getContainer()->set(WordTranslationServiceInterface::class, $translationServiceMock);

        $this->client->request(
            'POST',
            '/api/v1/words/translate',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode([
                            'word' => 'programista',
                            'sourceLanguage' => 'pl-PL',
                            'targetLanguage' => 'en-US'
                        ])
        );

        $this->assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('developer', $data['data']['translation']);
    }

    public function testGetCategoriesSuccess(): void
    {
        $this->client->request('GET', '/api/v1/words/categories');

        $this->assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertIsArray($data['data']);
    }

    public function testTranslateValidationFailure(): void
    {
        $this->loginAsTestUser();

        $this->client->request(
            'POST',
            '/api/v1/words/translate',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode([
                            'word' => '',
                            'sourceLanguage' => 'pl',
                        ])
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('error', $data['status']);
    }
}