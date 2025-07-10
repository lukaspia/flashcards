<?php

namespace App\Tests\Factory;

use App\Entity\Lesson;
use App\Entity\User;
use App\Factory\LessonFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LessonFactoryTest extends TestCase
{
    public function testCreateFromRequestDataSuccess(): void
    {
        $serializer = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['denormalize'])
            ->getMockForAbstractClass();
        $validator = $this->createMock(ValidatorInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $data = ['name' => 'Test Lesson'];
        $user = $this->createMock(User::class);

        $lesson = new Lesson();

        $serializer->expects($this->once())
            ->method('denormalize')
            ->with($data, Lesson::class, 'json')
            ->willReturn($lesson);

        $validator->expects($this->once())
            ->method('validate')
            ->with($lesson)
            ->willReturn(new ConstraintViolationList());

        $factory = new LessonFactory($serializer, $validator, $entityManager);
        $result = $factory->createFromRequestData($data, $user);

        $this->assertSame($lesson, $result);
        $this->assertSame($user, $lesson->getUser());
    }

    public function testCreateFromRequestDataFailure(): void
    {
        $serializer = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['denormalize'])
            ->getMockForAbstractClass();
        $validator = $this->createMock(ValidatorInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $data = ['name' => 'Test Lesson'];
        $user = $this->createMock(User::class);

        $exception = new \Exception('Denormalization error');

        $serializer->expects($this->once())
            ->method('denormalize')
            ->willThrowException($exception);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to create lesson: ' . $exception->getMessage());

        $factory = new LessonFactory($serializer, $validator, $entityManager);
        $factory->createFromRequestData($data, $user);
    }

    public function testUpdateFromRequestDataSuccess(): void
    {
        $serializer = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['denormalize'])
            ->getMockForAbstractClass();
        $validator = $this->createMock(ValidatorInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $data = ['name' => 'Updated Lesson'];
        $lesson = new Lesson();
        $originalName = 'Original Lesson';
        $lesson->setName($originalName);

        $serializer->expects($this->once())
            ->method('denormalize')
            ->with(
                $data,
                Lesson::class,
                null,
                $this->callback(function ($context) use ($lesson) {
                    return $context['object_to_populate'] === $lesson &&
                           $context['groups'] == ['lesson:write'];
                })
            )
            ->willReturn($lesson);

        // After denormalization, the lesson's name is updated
        $lesson->setName($data['name']);

        $validator->expects($this->once())
            ->method('validate')
            ->with($lesson)
            ->willReturn(new ConstraintViolationList());

        $factory = new LessonFactory($serializer, $validator, $entityManager);
        $result = $factory->updateFromRequestData($lesson, $data);

        $this->assertSame($lesson, $result);
        $this->assertEquals($data['name'], $result->getName());
    }

    public function testUpdateFromRequestDataFailure(): void
    {
        $serializer = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['denormalize'])
            ->getMockForAbstractClass();
        $validator = $this->createMock(ValidatorInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $data = ['name' => 'Updated Lesson'];
        $lesson = new Lesson();

        $exception = new \Exception('Update error');

        $serializer->expects($this->once())
            ->method('denormalize')
            ->willThrowException($exception);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to update lesson: ' . $exception->getMessage());

        $factory = new LessonFactory($serializer, $validator, $entityManager);
        $factory->updateFromRequestData($lesson, $data);
    }
}
