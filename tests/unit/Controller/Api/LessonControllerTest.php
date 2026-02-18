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
    private $denormalizer;
    private $lessonServices;
    private $logger;
    private $validator;
    private $lessonRepository;
    private $controller;
    private $user;
    private $serializer;
    private $lessonFactory;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
        $this->lessonServices = $this->createMock(LessonServiceInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->lessonRepository = $this->createMock(LessonRepository::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
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
                                     $this->createMock(LessonFactoryInterface::class)
                                 ])
            ->onlyMethods(['getUser'])
            ->getMock();

        $controller->method('getUser')->willReturn(null);
        $controller->setContainer($container);

        $response = $controller->index($request);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertStringContainsString('Authentication required', $response->getContent());
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
                                     $this->createMock(LessonFactoryInterface::class)
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
                                     $this->createMock(LessonFactoryInterface::class)
                                 ])
            ->onlyMethods(['getUser', 'createResponse'])
            ->getMock();

        $controller->method('getUser')->willReturn(null);

        $controller->setContainer($container);

        $controller->method('createResponse')
            ->willReturnCallback(function ($data, $messages, $status) {
                return new JsonResponse([
                                            'status' => $status >= 200 && $status < 300 ? 'success' : 'error',
                                            'data' => $data,
                                            'message' => $messages
                                        ], $status);
            });

        $response = $controller->addLesson($request, $this->validator);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Authentication required.', $responseData['message'][0]);
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
                                     $this->createMock(LessonFactoryInterface::class)
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
                                     $lessonFactory
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
                                     $this->createMock(LessonFactoryInterface::class)
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
                                     $this->createMock(LessonFactoryInterface::class)
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
                                     $this->createMock(LessonFactoryInterface::class)
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
