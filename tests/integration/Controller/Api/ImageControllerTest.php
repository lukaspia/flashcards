<?php

declare(strict_types=1);


namespace App\Tests\integration\Controller\Api;


use App\Entity\Lesson;
use App\Entity\User;
use App\Entity\Word;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\EntityManagerInterface;

class ImageControllerTest extends WebTestCase
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

    public function testUploadImageSuccess(): void
    {
        $this->client->loginUser($this->testUser);
        $word = $this->createTestWord($this->testUser);

        $imagePath = sys_get_temp_dir() . '/test_image.png';
        $img = \imagecreatetruecolor(10, 10);
        imagepng($img, $imagePath);
        imagedestroy($img);

        $uploadedFile = new UploadedFile(
            $imagePath,
            'test_image.png',
            'image/png',
            null,
            true
        );

        $this->client->request(
            'POST',
            '/api/v1/images/upload',
            ['word' => $word->getId()],
            ['image' => $uploadedFile]
        );

        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testDeleteImageSuccess(): void
    {
        $this->client->loginUser($this->testUser);
        $word = $this->createTestWord($this->testUser);

        $reflection = new \ReflectionClass($word);
        $property = $reflection->getProperty('image');
        $property->setAccessible(true);
        $property->setValue($word, 'uploads/test.jpg');

        $this->entityManager->flush();

        $this->client->request('DELETE', '/api/v1/images/' . $word->getId());

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    private function createTestWord(User $owner): Word
    {
        $lesson = new Lesson();
        $lesson->setName('Lesson for ' . $owner->getUserIdentifier());
        $lesson->setUser($owner);

        $refLesson = new \ReflectionClass($lesson);
        if ($refLesson->hasProperty('addDate')) {
            $prop = $refLesson->getProperty('addDate');
            $prop->setAccessible(true);
            $prop->setValue($lesson, new \DateTime());
        }
        $this->entityManager->persist($lesson);

        $word = new Word();

        $word->setBasicWord('Samochód');
        $word->setTranslation('Car');
        $word->setLesson($lesson);

        $reflection = new \ReflectionClass($word);
        $seqProp = $reflection->getProperty('sequence');
        $seqProp->setAccessible(true);
        $seqProp->setValue($word, 1);

        $this->entityManager->persist($word);
        $this->entityManager->flush();

        return $word;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}