<?php

declare(strict_types=1);


namespace App\Tests\integration\Controller\Api;

use App\Entity\Lesson;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class LessonControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private User $testUser;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();

        $userRepo = $this->entityManager->getRepository(User::class);
        $user = $userRepo->findOneBy(['username' => 'lukasz_test']);

        if (!$user) {
            $user = new User();
            $user->setUsername('lukasz_test');
            $user->setPassword('test_pass');
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $this->testUser = $user;
    }

    public function testIndexRequiresAuthentication(): void
    {
        $this->client->request('GET', '/api/v1/lessons');
        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testAddLessonSuccess(): void
    {
        $this->client->loginUser($this->testUser);

        $payload = [
            'name' => 'Lekcja przez FormData',
            'sourceLanguage' => 'pl-PL',
            'targetLanguage' => 'en-US'
        ];

        $this->client->request(
            'POST',
            '/api/v1/lessons',
            $payload
        );

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
    }

    public function testGetSingleLessonSuccess(): void
    {
        $lesson = new Lesson();
        $lesson->setName('Testowa lekcja');
        $lesson->setUser($this->testUser);

        $reflection = new \ReflectionClass($lesson);
        $property = $reflection->getProperty('addDate');
        $property->setAccessible(true);
        $property->setValue($lesson, new \DateTime());

        $this->entityManager->persist($lesson);
        $this->entityManager->flush();

        $this->client->loginUser($this->testUser);
        $this->client->request('GET', '/api/v1/lessons/' . $lesson->getId());

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testCannotAccessOthersLesson(): void
    {
        $otherUser = new User();
        $otherUser->setUsername('other_user_' . uniqid());
        $otherUser->setPassword('pass');
        $this->entityManager->persist($otherUser);

        $lesson = new Lesson();
        $lesson->setName('Cudza lekcja');
        $lesson->setUser($otherUser);

        $reflection = new \ReflectionClass($lesson);
        $property = $reflection->getProperty('addDate');
        $property->setAccessible(true);
        $property->setValue($lesson, new \DateTime());

        $this->entityManager->persist($lesson);
        $this->entityManager->flush();

        $this->client->loginUser($this->testUser);
        $this->client->request('GET', '/api/v1/lessons/' . $lesson->getId());

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testRemoveLessonSuccess(): void
    {
        $lesson = new Lesson();
        $lesson->setName('Do usunięcia');
        $lesson->setUser($this->testUser);

        $reflection = new \ReflectionClass($lesson);
        $property = $reflection->getProperty('addDate');
        $property->setAccessible(true);
        $property->setValue($lesson, new \DateTime());

        $this->entityManager->persist($lesson);
        $this->entityManager->flush();

        $lessonId = $lesson->getId();

        $this->client->loginUser($this->testUser);
        $this->client->request('DELETE', '/api/v1/lessons/' . $lessonId);

        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}