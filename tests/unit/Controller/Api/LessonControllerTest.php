<?php

namespace App\Tests\Controller\Api;

use App\Controller\Api\LessonController;
use App\Entity\Lesson;
use App\Entity\User;
use App\Repository\LessonRepository;
use App\Service\Lesson\LessonServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Factory\LessonFactoryInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class LessonControllerTest extends WebTestCase
{
    private $entityManager;
    private $lessonServices;
    private $logger;
    private $validator;
    private $lessonRepository;
    private $controller;
    private $user;
    private SerializerInterface $serializer;
    private $lessonFactory;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->lessonServices = $this->createMock(LessonServiceInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->lessonRepository = $this->createMock(LessonRepository::class);
        // Prosta implementacja SerializerInterface na potrzeby testów –
        // wystarczy nam obsługa denormalize() dla UpdateLessonDTO.
        $this->serializer = new class implements SerializerInterface {
            public function serialize($data, string $format, array $context = []): string
            {
                return '';
            }

            public function deserialize($data, string $type, string $format, array $context = []): mixed
            {
                return null;
            }

            public function denormalize($data, string $type, string $format = null, array $context = [])
            {
                if ($type === \App\DTO\UpdateLessonDTO::class && is_array($data)) {
                    return new \App\DTO\UpdateLessonDTO(
                        $data['id'] ?? 1,
                        $data['name'] ?? 'Updated Lesson',
                        $data['sourceLanguage'] ?? 'pl-PL',
                        $data['targetLanguage'] ?? 'en-US'
                    );
                }
                if ($type === \App\DTO\AddLessonDTO::class && is_array($data)) {
                    return new \App\DTO\AddLessonDTO(
                        $data['name'] ?? 'Test Lesson',
                        $data['sourceLanguage'] ?? 'pl-PL',
                        $data['targetLanguage'] ?? 'en-US'
                    );
                }

                return null;
            }

            public function normalize($object, string $format = null, array $context = []): array|string|int|float|bool
            {
                return [];
            }

            public function encode($data, string $format, array $context = []): string
            {
                return '';
            }

            public function decode(string $data, string $format, array $context = []): mixed
            {
                return null;
            }

            public function supportsEncoding(string $format): bool
            {
                return true;
            }

            public function supportsDecoding(string $format): bool
            {
                return true;
            }

            public function supportsDenormalization($data, string $type, string $format = null): bool
            {
                return true;
            }

            public function supportsNormalization($data, string $format = null): bool
            {
                return true;
            }
        };
        $this->lessonFactory = $this->createMock(LessonFactoryInterface::class);

        $this->user = new User();
        $this->user->setId(1);
        $this->user->setUsername('exampleuser');

        $this->entityManager->method('getRepository')
            ->with(Lesson::class)
            ->willReturn($this->lessonRepository);

        $this->controller = $this->getMockBuilder(LessonController::class)
            ->setConstructorArgs([
                                     $this->entityManager,
                                     $this->lessonServices,
                                     $this->logger,
                                     $this->lessonFactory,
                                     $this->validator,
                                     $this->serializer,
                                 ])
            ->onlyMethods(['getUser', 'createResponse'])
            ->getMock();

        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->method('getParameter')
            ->with('pagination_default_limit')
            ->willReturn(10);
        $this->controller->setContainer($container);
    }

    public function testIndexWithoutAuthentication()
    {
        $request = new Request();

        $parameterBag = $this->createMock(\Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface::class);
        $parameterBag->method('get')
            ->with('pagination_default_limit')
            ->willReturn(10);

        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->method('has')
            ->willReturnCallback(function($id) {
                return $id === 'parameter_bag';
            });
        $container->method('get')
            ->willReturnCallback(function($id) use ($parameterBag) {
                if ($id === 'parameter_bag') {
                    return $parameterBag;
                }
                return null;
            });

        $controller = $this->getMockBuilder(LessonController::class)
            ->setConstructorArgs([
                                     $this->entityManager,
                                     $this->lessonServices,
                                     $this->logger,
                                     $this->createMock(LessonFactoryInterface::class),
                                     $this->validator,
                                     $this->serializer,
                                 ])
            ->onlyMethods(['getUser'])
            ->getMock();

        $controller->method('getUser')->willReturn(null);
        $controller->setContainer($container);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Authentication required.');

        $controller->index($request);
    }

    public function testIndexWithAuthentication()
    {
        $request = new Request();
        $request->query->set('page', 2);

        $lessons = [
            new Lesson(),
            new Lesson(),
        ];

        $paginationData = [
            'lessons' => $lessons,
            'page' => 2,
            'totalItems' => 25,
            'totalPages' => 3
        ];

        $this->lessonServices->expects($this->once())
            ->method('getUserLessonsWithPagination')
            ->with($this->user, 2, 10)
            ->willReturn($paginationData);

        $parameterBag = $this->createMock(\Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface::class);
        $parameterBag->method('get')
            ->with('pagination_default_limit')
            ->willReturn(10);

        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->method('has')
            ->willReturnCallback(function($id) {
                return $id === 'parameter_bag';
            });
        $container->method('get')
            ->willReturnCallback(function($id) use ($parameterBag) {
                if ($id === 'parameter_bag') {
                    return $parameterBag;
                }
                return null;
            });

        $controller = $this->getMockBuilder(LessonController::class)
            ->setConstructorArgs([
                                     $this->entityManager,
                                     $this->lessonServices,
                                     $this->logger,
                                     $this->createMock(LessonFactoryInterface::class),
                                     $this->validator,
                                     $this->serializer,
                                 ])
            ->onlyMethods(['getUser', 'createResponse'])
            ->getMock();

        $controller->method('getUser')->willReturn($this->user);
        $controller->expects($this->once())
            ->method('createResponse')
            ->with(
                $paginationData,
                [],
                Response::HTTP_OK,
                ['groups' => 'lesson:read']
            )
            ->willReturn(new \Symfony\Component\HttpFoundation\JsonResponse(['success' => true], Response::HTTP_OK));

        $controller->setContainer($container);

        $response = $controller->index($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAddLessonWithoutAuthentication(): void
    {
        $content = json_encode(['name' => 'Test Lesson']);
        $request = Request::create(
            '/api/lessons',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $content
        );

        $container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->set('serializer', $this->serializer);
        $container->set('logger', $this->logger);

        $controller = $this->getMockBuilder(LessonController::class)
            ->setConstructorArgs([
                                     $this->entityManager,
                                     $this->lessonServices,
                                     $this->logger,
                                     $this->createMock(LessonFactoryInterface::class),
                                     $this->validator,
                                     $this->serializer,
                                 ])
            ->onlyMethods(['getUser'])
            ->getMock();

        $controller->method('getUser')->willReturn(null);

        $controller->setContainer($container);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Authentication required.');

        $controller->addLesson($request);
    }

    public function testAddLessonWithValidationErrors(): void
    {
        $content = json_encode(['name' => '']);
        $request = Request::create(
            '/api/lessons',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $content
        );

        $violation = $this->createMock(ConstraintViolation::class);
        $violation->method('getMessage')->willReturn('This value should not be blank.');
        $violation->method('getPropertyPath')->willReturn('name');

        $errors = new ConstraintViolationList([$violation]);

        $this->validator->method('validate')
            ->willReturn($errors);

        $container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->set('serializer', $this->serializer);
        $container->set('logger', $this->logger);

        $controller = $this->getMockBuilder(LessonController::class)
            ->setConstructorArgs([
                                     $this->entityManager,
                                     $this->lessonServices,
                                     $this->logger,
                                     $this->createMock(LessonFactoryInterface::class),
                                     $this->validator,
                                     $this->serializer,
                                 ])
            ->onlyMethods(['getUser', 'createResponse'])
            ->getMock();

        $controller->method('getUser')->willReturn($this->user);

        $controller->expects($this->once())
            ->method('createResponse')
            ->with(
                null,
                $this->isType('array'),
                Response::HTTP_BAD_REQUEST
            )
            ->willReturnCallback(function ($data, $messages, $status) {
                return new JsonResponse([
                                            'status' => 'error',
                                            'data' => $data,
                                            'message' => $messages
                                        ], $status);
            });

        $controller->setContainer($container);

        $response = $controller->addLesson($request, $this->validator);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertIsArray($responseData['message']);
    }

    public function testAddLessonSuccess(): void
    {
        $request = new Request([], [
            'name' => 'Test Lesson',
            'sourceLanguage' => 'pl-PL',
            'targetLanguage' => 'en-US',
        ]);

        $lesson = new Lesson();
        $lesson->setName('Test Lesson');

        $this->lessonFactory->expects($this->once())
            ->method('createFromRequestData')
            ->with($request->request->all(), $this->user)
            ->willReturn($lesson);

        $this->lessonServices->expects($this->once())
            ->method('addLesson')
            ->with($lesson);

        $this->controller->expects($this->any())
            ->method('getUser')
            ->willReturn($this->user);

        $this->controller->expects($this->once())
            ->method('createResponse')
            ->with(
                ['lesson' => $lesson],
                ['Lesson created successfully'],
                Response::HTTP_CREATED,
                ['groups' => Lesson::LESSON_READ_GROUP]
            )
            ->willReturn(new JsonResponse(
                             ['lesson' => ['name' => 'Test Lesson'], 'message' => ['Lesson created successfully']],
                             Response::HTTP_CREATED
                         ));

        $response = $this->controller->addLesson($request);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Test Lesson', $responseData['lesson']['name']);
    }

    public function testAddLessonException()
    {
        $request = new Request();
        $request->setMethod('POST');
        $request->request->set('title', 'Test Lesson');
        $request->request->set('name', 'Test Lesson');
        $request->request->set('sourceLanguage', 'pl-PL');
        $request->request->set('targetLanguage', 'en-US');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $lessonServices = $this->createMock(LessonServiceInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $lessonFactory = $this->createMock(LessonFactoryInterface::class);

        $lessonFactory->method('createFromRequestData')
            ->willThrowException(new \RuntimeException('Test exception'));

        $controller = $this->getMockBuilder(LessonController::class)
            ->setConstructorArgs([
                                     $entityManager,
                                     $lessonServices,
                                     $logger,
                                     $lessonFactory,
                                     $this->validator,
                                     $this->serializer,
                                 ])
            ->onlyMethods(['getUser', 'createResponse'])
            ->getMock();

        $controller->method('getUser')->willReturn($this->user);
        $controller->expects($this->once())
            ->method('createResponse')
            ->with(
                null,
                ['Unable to create lesson. Please check your data and try again.'],
                Response::HTTP_BAD_REQUEST
            )
            ->willReturn(new \Symfony\Component\HttpFoundation\JsonResponse(
                             ['error' => 'Lesson not created'],
                             Response::HTTP_BAD_REQUEST
                         ));

        $parameterBag = $this->createMock(\Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface::class);
        $parameterBag->method('get')
            ->with('pagination_default_limit')
            ->willReturn(10);

        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->method('has')
            ->willReturnCallback(function($id) {
                return $id === 'parameter_bag';
            });
        $container->method('get')
            ->willReturnCallback(function($id) use ($parameterBag) {
                return $id === 'parameter_bag' ? $parameterBag : null;
            });

        $controller->setContainer($container);

        $response = $controller->addLesson($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testUpdateLessonWithoutAuthentication(): void
    {
        $content = json_encode(['id' => 1, 'name' => 'Updated Lesson', 'sourceLanguage' => 'pl-PL', 'targetLanguage' => 'en-US']);
        $request = Request::create(
            '/api/lessons',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $content
        );

        $container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->set('serializer', $this->serializer);
        $container->set('logger', $this->logger);

        $controller = $this->getMockBuilder(LessonController::class)
            ->setConstructorArgs([
                                     $this->entityManager,
                                     $this->lessonServices,
                                     $this->logger,
                                     $this->createMock(LessonFactoryInterface::class),
                                     $this->validator,
                                     $this->serializer,
                                 ])
            ->onlyMethods(['getUser'])
            ->getMock();

        $controller->method('getUser')->willReturn(null);

        $controller->setContainer($container);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Authentication required.');

        $controller->updateLesson($request);
    }

    public function testUpdateLessonWithValidationErrors(): void
    {
        $content = json_encode(['id' => 1, 'name' => '']); // Missing required fields
        $request = Request::create(
            '/api/lessons',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $content
        );

        $container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->set('serializer', $this->serializer);
        $container->set('logger', $this->logger);
        $container->set('validator', $this->validator);

        $controller = $this->getMockBuilder(LessonController::class)
            ->setConstructorArgs([
                                     $this->entityManager,
                                     $this->lessonServices,
                                     $this->logger,
                                     $this->createMock(LessonFactoryInterface::class),
                                     $this->validator,
                                     $this->serializer,
                                 ])
            ->onlyMethods(['getUser', 'createResponse'])
            ->addMethods(['get'])
            ->getMock();

        $controller->method('getUser')->willReturn($this->user);
        $controller->method('get')
            ->with('validator')
            ->willReturn($this->validator);

        // Set up validator to return errors for empty name
        $violation1 = $this->createMock(\Symfony\Component\Validator\ConstraintViolation::class);
        $violation1->method('getMessage')->willReturn('Name is required');

        $violation2 = $this->createMock(\Symfony\Component\Validator\ConstraintViolation::class);
        $violation2->method('getMessage')->willReturn('Source language is required');

        $errors = new ConstraintViolationList([$violation1, $violation2]);
        $this->validator->method('validate')
            ->willReturn($errors);

        $controller->expects($this->once())
            ->method('createResponse')
            ->with(
                null,
                $this->logicalAnd(
                    $this->isType('array'),
                    $this->callback(function($messages) {
                        return is_array($messages) &&
                               in_array('Name is required', $messages) &&
                               in_array('Source language is required', $messages);
                    })
                ),
                Response::HTTP_BAD_REQUEST
            )
            ->willReturnCallback(function ($data, $messages, $status) {
                return new JsonResponse([
                                            'status' => 'error',
                                            'data' => $data,
                                            'message' => $messages
                                        ], $status);
            });

        $controller->setContainer($container);

        $response = $controller->updateLesson($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testUpdateLessonUnauthorized(): void
    {
        $content = json_encode(['id' => 1, 'name' => 'Updated Lesson', 'sourceLanguage' => 'pl-PL', 'targetLanguage' => 'en-US']);
        $request = Request::create(
            '/api/lessons',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $content
        );

        $existingLesson = new Lesson();
        $existingLesson->setId(1);
        $existingLesson->setUser($this->createMock(\App\Entity\User::class)); // Different user

        $container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->set('serializer', $this->serializer);
        $container->set('logger', $this->logger);

        $controller = $this->getMockBuilder(LessonController::class)
            ->setConstructorArgs([
                                     $this->entityManager,
                                     $this->lessonServices,
                                     $this->logger,
                                     $this->createMock(LessonFactoryInterface::class),
                                     $this->validator,
                                     $this->serializer,
                                 ])
            ->onlyMethods(['getUser', 'isGranted', 'createResponse'])
            ->addMethods(['get'])
            ->getMock();

        $controller->method('getUser')->willReturn($this->user);
        $controller->method('get')
            ->with('validator')
            ->willReturn($this->validator);
        $controller->method('isGranted')
            ->with('LESSON_EDIT', $existingLesson)
            ->willReturn(false);

        $this->entityManager->getRepository(Lesson::class)
            ->method('find')
            ->with(1)
            ->willReturn($existingLesson);

        $controller->expects($this->once())
            ->method('createResponse')
            ->with(
                null,
                ['You are not authorized to edit this lesson.'],
                Response::HTTP_FORBIDDEN
            )
            ->willReturn(new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN));

        $controller->setContainer($container);

        $response = $controller->updateLesson($request);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testUpdateLessonSuccess(): void
    {
        $content = json_encode(['id' => 1, 'name' => 'Updated Lesson', 'sourceLanguage' => 'pl-PL', 'targetLanguage' => 'en-US']);
        $request = Request::create(
            '/api/lessons',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $content
        );

        $existingLesson = new Lesson();
        $existingLesson->setId(1);
        $existingLesson->setName('Original Lesson');
        $existingLesson->setUser($this->user);

        $updatedLesson = new Lesson();
        $updatedLesson->setId(1);
        $updatedLesson->setName('Updated Lesson');

        $lessonFactory = $this->createMock(LessonFactoryInterface::class);
        $lessonFactory->expects($this->once())
            ->method('updateFromRequestData')
            ->with($existingLesson, [
                'name' => 'Updated Lesson',
                'sourceLanguage' => 'pl-PL',
                'targetLanguage' => 'en-US'
            ])
            ->willReturn($updatedLesson);

        $this->lessonServices->expects($this->once())
            ->method('updateLesson')
            ->with($updatedLesson);

        $container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->set('serializer', $this->serializer);
        $container->set('logger', $this->logger);

        $controller = $this->getMockBuilder(LessonController::class)
            ->setConstructorArgs([
                                     $this->entityManager,
                                     $this->lessonServices,
                                     $this->logger,
                                     $lessonFactory,
                                     $this->validator,
                                     $this->serializer,
                                 ])
            ->onlyMethods(['getUser', 'isGranted', 'createResponse'])
            ->addMethods(['get'])
            ->getMock();

        $controller->method('getUser')->willReturn($this->user);
        $controller->method('get')
            ->with('validator')
            ->willReturn($this->validator);
        $controller->method('isGranted')
            ->with('LESSON_EDIT', $existingLesson)
            ->willReturn(true);

        $this->entityManager->getRepository(Lesson::class)
            ->method('find')
            ->with(1)
            ->willReturn($existingLesson);

        $controller->expects($this->once())
            ->method('createResponse')
            ->with(
                ['lesson' => $updatedLesson],
                ['Lesson updated successfully'],
                Response::HTTP_OK,
                ['groups' => Lesson::LESSON_READ_GROUP]
            )
            ->willReturn(new JsonResponse(
                             ['lesson' => ['name' => 'Updated Lesson'], 'message' => ['Lesson updated successfully']],
                             Response::HTTP_OK
                         ));

        $controller->setContainer($container);

        $response = $controller->updateLesson($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testUpdateLessonException(): void
    {
        $content = json_encode(['id' => 1, 'name' => 'Updated Lesson', 'sourceLanguage' => 'pl-PL', 'targetLanguage' => 'en-US']);
        $request = Request::create(
            '/api/lessons',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $content
        );

        $existingLesson = new Lesson();
        $existingLesson->setId(1);
        $existingLesson->setUser($this->user);

        $lessonFactory = $this->createMock(LessonFactoryInterface::class);
        $lessonFactory->expects($this->once())
            ->method('updateFromRequestData')
            ->with($existingLesson, [
                'name' => 'Updated Lesson',
                'sourceLanguage' => 'pl-PL',
                'targetLanguage' => 'en-US'
            ])
            ->willThrowException(new \RuntimeException('Test exception'));

        $container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->set('serializer', $this->serializer);
        $container->set('logger', $this->logger);

        $controller = $this->getMockBuilder(LessonController::class)
            ->setConstructorArgs([
                                     $this->entityManager,
                                     $this->lessonServices,
                                     $this->logger,
                                     $lessonFactory,
                                     $this->validator,
                                     $this->serializer,
                                 ])
            ->onlyMethods(['getUser', 'isGranted', 'createResponse'])
            ->addMethods(['get'])
            ->getMock();

        $controller->method('getUser')->willReturn($this->user);
        $controller->method('get')
            ->with('validator')
            ->willReturn($this->validator);
        $controller->method('isGranted')
            ->with('LESSON_EDIT', $existingLesson)
            ->willReturn(true);

        $this->entityManager->getRepository(Lesson::class)
            ->method('find')
            ->with(1)
            ->willReturn($existingLesson);

        $controller->expects($this->once())
            ->method('createResponse')
            ->with(
                null,
                ['Invalid data: Test exception'],
                Response::HTTP_BAD_REQUEST
            )
            ->willReturn(new \Symfony\Component\HttpFoundation\JsonResponse(
                             ['error' => 'Lesson not updated'],
                             Response::HTTP_BAD_REQUEST
                         ));

        $controller->setContainer($container);

        $response = $controller->updateLesson($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testRemoveLessonUnauthorized()
    {
        $lesson = new Lesson();

        $parameterBag = $this->createMock(\Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface::class);
        $parameterBag->method('get')
            ->with('pagination_default_limit')
            ->willReturn(10);

        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->method('has')
            ->willReturnCallback(function($id) {
                return $id === 'parameter_bag';
            });
        $container->method('get')
            ->willReturnCallback(function($id) use ($parameterBag) {
                if ($id === 'parameter_bag') {
                    return $parameterBag;
                }
                return null;
            });

        $controller = $this->getMockBuilder(LessonController::class)
            ->setConstructorArgs([
                                     $this->entityManager,
                                     $this->lessonServices,
                                     $this->logger,
                                     $this->createMock(LessonFactoryInterface::class),
                                     $this->validator,
                                     $this->serializer,
                                 ])
            ->onlyMethods(['isGranted', 'createResponse'])
            ->getMock();

        $controller->method('isGranted')
            ->with('LESSON_DELETE', $lesson)
            ->willReturn(false);

        $controller->expects($this->once())
            ->method('createResponse')
            ->with(
                null,
                ['You are not authorized to delete this lesson.'],
                Response::HTTP_FORBIDDEN
            )
            ->willReturn(new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN));

        $controller->setContainer($container);

        $response = $controller->removeLesson($lesson);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testRemoveLessonSuccess()
    {
        $lesson = new Lesson();

        $parameterBag = $this->createMock(\Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface::class);
        $parameterBag->method('get')
            ->with('pagination_default_limit')
            ->willReturn(10);

        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->method('has')
            ->willReturnCallback(function($id) {
                return $id === 'parameter_bag';
            });
        $container->method('get')
            ->willReturnCallback(function($id) use ($parameterBag) {
                if ($id === 'parameter_bag') {
                    return $parameterBag;
                }
                return null;
            });

        $controller = $this->getMockBuilder(LessonController::class)
            ->setConstructorArgs([
                                     $this->entityManager,
                                     $this->lessonServices,
                                     $this->logger,
                                     $this->createMock(LessonFactoryInterface::class),
                                     $this->validator,
                                     $this->serializer,
                                 ])
            ->onlyMethods(['isGranted', 'createResponse'])
            ->getMock();

        $controller->method('isGranted')
            ->with('LESSON_DELETE', $lesson)
            ->willReturn(true);

        $this->lessonServices->expects($this->once())
            ->method('removeLesson')
            ->with($lesson);

        $this->logger->expects($this->once())
            ->method('info')
            ->with('Lesson removed successfully', $this->isType('array'));

        $controller->expects($this->once())
            ->method('createResponse')
            ->with(
                null,
                ['Lesson remove successfully'],
                Response::HTTP_NO_CONTENT
            )
            ->willReturn(new \Symfony\Component\HttpFoundation\JsonResponse(null, Response::HTTP_NO_CONTENT));

        $controller->setContainer($container);

        $response = $controller->removeLesson($lesson);

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testRemoveLessonError()
    {
        $lesson = new Lesson();
        $exception = new \Exception('Database error');

        $parameterBag = $this->createMock(\Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface::class);
        $parameterBag->method('get')
            ->with('pagination_default_limit')
            ->willReturn(10);

        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->method('has')
            ->willReturnCallback(function($id) {
                return $id === 'parameter_bag';
            });
        $container->method('get')
            ->willReturnCallback(function($id) use ($parameterBag) {
                if ($id === 'parameter_bag') {
                    return $parameterBag;
                }
                return null;
            });

        $controller = $this->getMockBuilder(LessonController::class)
            ->setConstructorArgs([
                                     $this->entityManager,
                                     $this->lessonServices,
                                     $this->logger,
                                     $this->createMock(LessonFactoryInterface::class),
                                     $this->validator,
                                     $this->serializer,
                                 ])
            ->onlyMethods(['isGranted', 'createResponse'])
            ->getMock();

        $controller->method('isGranted')
            ->with('LESSON_DELETE', $lesson)
            ->willReturn(true);

        $this->lessonServices->expects($this->once())
            ->method('removeLesson')
            ->with($lesson)
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Lesson remove error: Database error');

        $controller->expects($this->once())
            ->method('createResponse')
            ->with(
                null,
                ['Lesson remove error: Database error'],
                Response::HTTP_BAD_REQUEST
            )
            ->willReturn(new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'Database error'], Response::HTTP_BAD_REQUEST));

        $controller->setContainer($container);

        $response = $controller->removeLesson($lesson);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }
}
