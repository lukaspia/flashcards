<?php

declare(strict_types=1);

namespace App\Tests\Service\Lesson;

use App\Entity\Lesson;
use App\Event\AddLessonEvent;
use App\Service\Lesson\LessonServices;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LessonServicesTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private EventDispatcherInterface $eventDispatcher;
    private LessonServices $lessonServices;
    private Lesson $lesson;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->lessonServices = new LessonServices(
            $this->entityManager,
            $this->validator,
            $this->eventDispatcher
        );

        $this->lesson = new Lesson();
    }

    public function testAddLessonSuccess(): void
    {
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->lesson)
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->lesson);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(function ($event) {
                    return $event instanceof AddLessonEvent && $event->getLesson() === $this->lesson;
                }),
                AddLessonEvent::NAME
            );

        $result = $this->lessonServices->addLesson($this->lesson);

        $this->assertSame($this->lesson, $result);
    }

    public function testAddLessonValidationFailure(): void
    {
        $errors = $this->createMock(ConstraintViolationList::class);
        $errors->method('__toString')->willReturn('Validation failed');
        $errors->method('count')->willReturn(1);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->lesson)
            ->willReturn($errors);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $this->eventDispatcher->expects($this->never())->method('dispatch');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Validation failed');

        $this->lessonServices->addLesson($this->lesson);
    }

    public function testRemoveLesson(): void
    {
        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($this->lesson);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(function ($event) {
                    return $event instanceof AddLessonEvent && $event->getLesson() === $this->lesson;
                }),
                AddLessonEvent::NAME
            );

        $result = $this->lessonServices->removeLesson($this->lesson);

        $this->assertSame($this->lesson, $result);
    }
}
