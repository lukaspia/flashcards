<?php

namespace App\Tests\Controller\Api;

use App\Controller\Api\LessonController;
use App\Entity\Lesson;
use App\Entity\User;
use App\Repository\LessonRepository;
use App\Service\Lesson\LessonServices;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
        $this->lessonServices = $this->createMock(LessonServices::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->lessonRepository = $this->createMock(LessonRepository::class);

        $this->user = new User();
        $this->user->setId(1);
        $this->user->setUsername('exampleuser');

        $this->entityManager->method('getRepository')
            ->with(Lesson::class)
            ->willReturn($this->lessonRepository);

        $this->controller = new LessonController(
            $this->entityManager,
            $this->denormalizer,
            $this->lessonServices,
            $this->logger
        );

        // Set container with parameters
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
                                     $this->denormalizer,
                                     $this->lessonServices,
                                     $this->logger
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

        $this->lessonRepository->expects($this->once())
            ->method('findPaginatedLessons')
            ->with(['user' => $this->user], ['id' => 'DESC'], 10, 2)
            ->willReturn($lessons);

        $this->lessonRepository->expects($this->once())
            ->method('countLessonsByCriteria')
            ->with(['user' => $this->user])
            ->willReturn(25);

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
                                     $this->denormalizer,
                                     $this->lessonServices,
                                     $this->logger
                                 ])
            ->onlyMethods(['getUser', 'createResponse'])
            ->getMock();

        $controller->method('getUser')->willReturn($this->user);
        $controller->expects($this->once())
            ->method('createResponse')
            ->with(
                [
                    'lessons' => $lessons,
                    'page' => 2,
                    'totalItems' => 25,
                    'totalPages' => 3
                ],
                [],
                Response::HTTP_OK,
                ['groups' => 'lesson:read']
            )
            ->willReturn(new \Symfony\Component\HttpFoundation\JsonResponse(['success' => true], Response::HTTP_OK));

        $controller->setContainer($container);

        $response = $controller->index($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAddLessonWithoutAuthentication()
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
                                     $this->denormalizer,
                                     $this->lessonServices,
                                     $this->logger
                                 ])
            ->onlyMethods(['getUser'])
            ->getMock();

        $controller->method('getUser')->willReturn(null);
        $controller->setContainer($container);

        $lesson = new Lesson();
        $this->denormalizer->method('denormalize')
            ->willReturn($lesson);

        $response = $controller->addLesson($request, $this->validator);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertStringContainsString('Authentication required', $response->getContent());
    }

    public function testAddLessonWithValidationErrors()
    {
        $request = new Request();
        $request->request->set('title', 'Test Lesson');

        $lesson = new Lesson();
        $this->denormalizer->method('denormalize')
            ->willReturn($lesson);

        $violation = $this->createMock(ConstraintViolation::class);
        $violation->method('getPropertyPath')->willReturn('title');
        $violation->method('getMessage')->willReturn('Title is too short');

        $violationList = new ConstraintViolationList([$violation]);

        $this->validator->method('validate')
            ->with($lesson)
            ->willReturn($violationList);

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
                                     $this->denormalizer,
                                     $this->lessonServices,
                                     $this->logger
                                 ])
            ->onlyMethods(['getUser', 'createResponse'])
            ->getMock();

        $controller->method('getUser')->willReturn($this->user);
        $controller->expects($this->once())
            ->method('createResponse')
            ->with(
                ['errors' => ['title' => 'Title is too short']],
                ['Validation failed'],
                Response::HTTP_BAD_REQUEST
            )
            ->willReturn(
                new \Symfony\Component\HttpFoundation\JsonResponse(
                    ['errors' => ['title' => 'Title is too short']],
                    Response::HTTP_BAD_REQUEST
                )
            );

        $controller->setContainer($container);

        $response = $controller->addLesson($request, $this->validator);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testAddLessonSuccess()
    {
        $request = new Request();
        $request->request->set('title', 'Test Lesson');

        $lesson = new Lesson();
        $this->denormalizer->method('denormalize')
            ->willReturn($lesson);

        $this->validator->method('validate')
            ->with($lesson)
            ->willReturn(new ConstraintViolationList());

        $this->lessonServices->expects($this->once())
            ->method('addLesson')
            ->with($lesson);

        $this->logger->expects($this->once())
            ->method('info')
            ->with('Lesson created successfully', ['lesson' => $lesson]);

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
                                     $this->denormalizer,
                                     $this->lessonServices,
                                     $this->logger
                                 ])
            ->onlyMethods(['getUser', 'createResponse'])
            ->getMock();

        $controller->method('getUser')->willReturn($this->user);
        $controller->expects($this->once())
            ->method('createResponse')
            ->with(
                ['lesson' => $lesson],
                ['Lesson created successfully'],
                Response::HTTP_CREATED,
                ['groups' => 'lesson:read']
            )
            ->willReturn(new \Symfony\Component\HttpFoundation\JsonResponse(['lesson' => []], Response::HTTP_CREATED));

        $controller->setContainer($container);

        $response = $controller->addLesson($request, $this->validator);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testAddLessonException()
    {
        $request = new Request();
        $request->request->set('title', 'Test Lesson');

        $this->denormalizer->method('denormalize')
            ->willThrowException(new \Exception('Denormalization error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Lesson not created: Denormalization error');

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
                                     $this->denormalizer,
                                     $this->lessonServices,
                                     $this->logger
                                 ])
            ->onlyMethods(['getUser', 'createResponse'])
            ->getMock();

        $controller->method('getUser')->willReturn($this->user);
        $controller->expects($this->once())
            ->method('createResponse')
            ->with(
                null,
                $this->callback(function ($messages) {
                    return $messages[0] === 'Lesson not created';
                }),
                Response::HTTP_BAD_REQUEST
            )
            ->willReturn(new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'Lesson not created'], Response::HTTP_BAD_REQUEST));

        $controller->setContainer($container);

        $response = $controller->addLesson($request, $this->validator);

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
                                     $this->denormalizer,
                                     $this->lessonServices,
                                     $this->logger
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
                                     $this->denormalizer,
                                     $this->lessonServices,
                                     $this->logger
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
            ->with('Lesson removed', ['lesson' => $lesson]);

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
                                     $this->denormalizer,
                                     $this->lessonServices,
                                     $this->logger
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
