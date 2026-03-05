<?php

namespace App\Tests\Service\Lesson;

use App\Entity\Lesson;
use App\Entity\User;
use App\Event\AddLessonEvent;
use App\Repository\LessonRepository;
use App\Service\Lesson\LessonService;
use App\Service\Lesson\WordImageServiceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LessonServiceTest extends TestCase
{
    private MockObject|EntityManagerInterface $entityManager;
    private MockObject|LessonRepository $lessonRepository;
    private MockObject|ValidatorInterface $validator;
    private MockObject|EventDispatcherInterface $eventDispatcher;
    private MockObject|WordImageServiceInterface $wordImageServices;
    private LessonService $service;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->lessonRepository = $this->createMock(LessonRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->wordImageServices = $this->createMock(WordImageServiceInterface::class);

        $this->entityManager->method('wrapInTransaction')
            ->will($this->returnCallback(fn($callback) => $callback($this->entityManager)));

        $this->service = new LessonService(
            $this->entityManager,
            $this->lessonRepository,
            $this->validator,
            $this->eventDispatcher,
            $this->wordImageServices
        );
    }

    public function testAddLessonSuccess(): void
    {
        $lesson = $this->createMock(Lesson::class);
        $lesson->method('getWords')->willReturn(new ArrayCollection());

        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $violations->method('count')->willReturn(0);
        $this->validator->method('validate')->willReturn($violations);

        $this->entityManager->expects($this->once())->method('persist')->with($lesson);
        $this->entityManager->expects($this->once())->method('flush');
        $this->wordImageServices->expects($this->once())->method('moveWordsImagesFromTemporary');

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(AddLessonEvent::class), AddLessonEvent::NAME);

        $result = $this->service->addLesson($lesson);

        $this->assertSame($lesson, $result);
    }

    public function testAddLessonThrowsExceptionOnInvalidData(): void
    {
        $lesson = $this->createMock(Lesson::class);

        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $violations->method('count')->willReturn(1);
        $violations->method('__toString')->willReturn('Błąd walidacji');

        $this->validator->method('validate')->willReturn($violations);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Błąd walidacji');

        $this->service->addLesson($lesson);
    }

    public function testGetUserLessonsWithPagination(): void
    {
        $user = $this->createMock(User::class);
        $limit = 10;
        $page = 1;

        $paginator = $this->getMockBuilder(\Doctrine\ORM\Tools\Pagination\Paginator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lessons = [new Lesson(), new Lesson()];
        $paginator->method('getIterator')->willReturn(new \ArrayIterator($lessons));
        $paginator->method('count')->willReturn(count($lessons));

        $this->lessonRepository->expects($this->once())
            ->method('getPaginatedLessons')
            ->with(['user' => $user], ['id' => 'DESC'], $limit, $page)
            ->willReturn($paginator);

        $result = $this->service->getUserLessonsWithPagination($user, $page, $limit);

        $this->assertArrayHasKey('lessons', $result);
        $this->assertEquals(2, $result['totalItems']);
        $this->assertCount(2, $result['lessons']);
        $this->assertEquals(1, $result['totalPages']);
    }
}