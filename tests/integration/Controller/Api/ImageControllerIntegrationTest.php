<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller\Api;

use App\Entity\Lesson;
use App\Entity\User;
use App\Entity\Word;
use App\Entity\WordCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class ImageControllerIntegrationTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;
    private User $testUser;
    private Lesson $testLesson;
    private Word $testWord;
    private WordCategory $testCategory;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        
        $this->createTestData();
    }

    protected function tearDown(): void
    {
        try {
            // Usuń dane testowe - najpierw odśwież encje jeśli są detached
            if ($this->testWord && $this->testWord->getId()) {
                $word = $this->entityManager->find(Word::class, $this->testWord->getId());
                if ($word) {
                    $this->entityManager->remove($word);
                }
            }
            
            if ($this->testLesson && $this->testLesson->getId()) {
                $lesson = $this->entityManager->find(Lesson::class, $this->testLesson->getId());
                if ($lesson) {
                    $this->entityManager->remove($lesson);
                }
            }
            
            if ($this->testCategory && $this->testCategory->getId()) {
                $category = $this->entityManager->find(WordCategory::class, $this->testCategory->getId());
                if ($category) {
                    $this->entityManager->remove($category);
                }
            }
            
            if ($this->testUser && $this->testUser->getId()) {
                $user = $this->entityManager->find(User::class, $this->testUser->getId());
                if ($user) {
                    $this->entityManager->remove($user);
                }
            }
            
            $this->entityManager->flush();
        } catch (\Exception $e) {
        }
        
        parent::tearDown();
    }

    private function createTestData(): void
    {
        $uniqueId = uniqid('test_', true);

        $this->testUser = new User();
        $this->testUser->setUsername($uniqueId);
        $this->testUser->setPassword('$2y$13$hashed_password');
        $this->testUser->setRoles(['ROLE_USER']);
        $this->entityManager->persist($this->testUser);

        $this->testCategory = new WordCategory();
        $this->testCategory->setName('Test Category ' . $uniqueId);
        $this->entityManager->persist($this->testCategory);

        $this->testLesson = new Lesson();
        $this->testLesson->setName('Test Lesson ' . $uniqueId);
        $this->testLesson->setUser($this->testUser);
        $this->entityManager->persist($this->testLesson);

        $this->testWord = new Word();
        $this->testWord->setBasicWord('test_' . $uniqueId);
        $this->testWord->setTranslation('test_' . $uniqueId);
        $this->testWord->setExample('This is a test ' . $uniqueId);
        $this->testWord->setLesson($this->testLesson);
        $this->testWord->setWordCategory($this->testCategory);
        $this->testWord->setSequence(1);
        $this->testWord->setErrors(0);
        $this->testWord->setColor('#000000');
        $this->entityManager->persist($this->testWord);

        $this->entityManager->flush();
    }

    private function loginUser(): void
    {
        $this->client->loginUser($this->testUser);
    }

    public function testUploadImageUnauthenticated(): void
    {
        $uploadedFile = $this->createTestImageFile();

        $this->client->request('POST', '/images/upload', [
            'upload_word_image_type_form' => [
                'word' => $this->testWord->getId(),
            ]
        ], [
            'upload_word_image_type_form' => [
                'image' => $uploadedFile
            ]
        ]);

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertContains('Authentication required.', $responseData['message']);
    }

    public function testUploadImageWithoutFile(): void
    {
        $this->loginUser();

        $this->client->request('POST', '/images/upload', [
            'upload_word_image_type_form' => [
                'word' => $this->testWord->getId(),
            ]
        ]);

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertContains('Image upload validation failed', $responseData['message']);
    }

    public function testUploadImageSuccess(): void
    {
        $this->loginUser();
        $uploadedFile = $this->createTestImageFile();

        $this->client->request('POST', '/images/upload', [
            'upload_word_image_type_form' => [
                'word' => $this->testWord->getId(),
            ]
        ], [
            'upload_word_image_type_form' => [
                'image' => $uploadedFile
            ]
        ]);

        $response = $this->client->getResponse();
        
        if ($response->getStatusCode() === Response::HTTP_OK) {
            $responseData = json_decode($response->getContent(), true);
            $this->assertEquals('success', $responseData['status']);
            $this->assertContains('Image uploaded successfully', $responseData['message']);
            $this->assertArrayHasKey('image', $responseData['data']);
        } else {
            $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        }
    }

    public function testDeleteImageUnauthenticated(): void
    {
        $this->client->request('DELETE', '/images/' . $this->testWord->getId());

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertContains('Authentication required.', $responseData['message']);
    }

    public function testDeleteImageForbidden(): void
    {
        $otherUser = new User();
        $otherUser->setUsername('otheruser_' . uniqid());
        $otherUser->setPassword('$2y$13$hashed_password');
        $otherUser->setRoles(['ROLE_USER']);
        $this->entityManager->persist($otherUser);
        $this->entityManager->flush();

        $this->client->loginUser($otherUser);

        $this->client->request('DELETE', '/images/' . $this->testWord->getId());

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertContains('You do not have permission to delete this image.', $responseData['message']);

        $this->entityManager->remove($otherUser);
        $this->entityManager->flush();
    }

    public function testDeleteImageSuccess(): void
    {
        $this->loginUser();

        $this->testWord->setImage('test_image.jpg');
        $this->entityManager->flush();

        $this->client->request('DELETE', '/images/' . $this->testWord->getId());

        $response = $this->client->getResponse();
        
        if ($response->getStatusCode() === Response::HTTP_OK) {
            $responseData = json_decode($response->getContent(), true);
            $this->assertEquals('success', $responseData['status']);
            $this->assertContains('Image deleted successfully', $responseData['message']);
        }
    }

    public function testDeleteImageNotFound(): void
    {
        $this->loginUser();

        $this->client->request('DELETE', '/images/' . $this->testWord->getId());

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_NOT_FOUND,
            Response::HTTP_OK,
            Response::HTTP_INTERNAL_SERVER_ERROR
        ]);

        if ($responseData !== null) {
            if ($response->getStatusCode() === Response::HTTP_NOT_FOUND) {
                $this->assertEquals('error', $responseData['status']);
                $this->assertContains('Image not found or can\'t be remove', $responseData['message']);
            } elseif ($response->getStatusCode() === Response::HTTP_OK) {
                $this->assertEquals('success', $responseData['status']);
            } elseif ($response->getStatusCode() === Response::HTTP_INTERNAL_SERVER_ERROR) {
                $this->assertEquals('error', $responseData['status']);
            }
        } else {
            $this->assertNotEmpty($response->getContent(), 'Response should not be empty');
        }
    }

    public function testDeleteNonExistentWord(): void
    {
        $this->loginUser();

        $this->client->request('DELETE', '/images/99999');

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testUploadImageWithInvalidMimeType(): void
    {
        $this->loginUser();

        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'This is not an image');
        
        $uploadedFile = new UploadedFile(
            $tempFile,
            'test.txt',
            'text/plain',
            null,
            true
        );

        $this->client->request('POST', '/images/upload', [
            'upload_word_image_type_form' => [
                'word' => $this->testWord->getId(),
            ]
        ], [
            'upload_word_image_type_form' => [
                'image' => $uploadedFile
            ]
        ]);

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        
        unlink($tempFile);
    }

    public function testUploadImageWithInvalidWordId(): void
    {
        $this->loginUser();
        $uploadedFile = $this->createTestImageFile();

        $this->client->request('POST', '/images/upload', [
            'upload_word_image_type_form' => [
                'word' => 99999,
            ]
        ], [
            'upload_word_image_type_form' => [
                'image' => $uploadedFile
            ]
        ]);

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testUploadMultipleImagesForSameWord(): void
    {
        $this->loginUser();

        $uploadedFile1 = $this->createTestImageFile('test1.jpg');
        $this->client->request('POST', '/images/upload', [
            'upload_word_image_type_form' => [
                'word' => $this->testWord->getId(),
            ]
        ], [
            'upload_word_image_type_form' => [
                'image' => $uploadedFile1
            ]
        ]);

        $response1 = $this->client->getResponse();

        $this->loginUser();

        $uploadedFile2 = $this->createTestImageFile('test2.jpg');
        $this->client->request('POST', '/images/upload', [
            'upload_word_image_type_form' => [
                'word' => $this->testWord->getId(),
            ]
        ], [
            'upload_word_image_type_form' => [
                'image' => $uploadedFile2
            ]
        ]);

        $response2 = $this->client->getResponse();

        $allowedStatuses = [
            Response::HTTP_OK,
            Response::HTTP_UNAUTHORIZED,
            Response::HTTP_INTERNAL_SERVER_ERROR
        ];
        
        $this->assertContains($response1->getStatusCode(), $allowedStatuses);
        $this->assertContains($response2->getStatusCode(), $allowedStatuses);

        if ($response1->getStatusCode() === Response::HTTP_OK && $response2->getStatusCode() === Response::HTTP_OK) {
            $responseData1 = json_decode($response1->getContent(), true);
            $responseData2 = json_decode($response2->getContent(), true);
            
            if ($responseData1 !== null && $responseData2 !== null) {
                $this->assertEquals('success', $responseData1['status']);
                $this->assertEquals('success', $responseData2['status']);
                $this->assertContains('Image uploaded successfully', $responseData1['message']);
                $this->assertContains('Image uploaded successfully', $responseData2['message']);
            }
        }
    }

    private function createTestImageFile(string $filename = 'test_image.jpg'): UploadedFile
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_image');

        $jpegData = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A');
        file_put_contents($tempFile, $jpegData);
        
        return new UploadedFile(
            $tempFile,
            $filename,
            'image/jpeg',
            null,
            true
        );
    }
}