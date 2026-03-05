<?php

namespace App\Tests\Factory;

use App\DTO\AddLessonDTO;
use App\DTO\UpdateLessonDTO;
use App\Entity\Lesson;
use App\Entity\Word;
use App\Entity\User;

// Importujemy konkretną klasę User
use App\Factory\LessonFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

interface FullSerializerInterface extends SerializerInterface
{
    public function denormalize($data, string $type, string $format = null, array $context = []);
}

class LessonFactoryTest extends TestCase
{
    private MockObject $serializer;
    private LessonFactory $factory;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(FullSerializerInterface::class);
        $this->factory = new LessonFactory($this->serializer);
    }

    public function testCreateFromDTO(): void
    {
        $dto = new AddLessonDTO('New Lesson');

        $user = $this->createMock(User::class);

        $this->serializer->expects($this->once())
            ->method('denormalize')
            ->willReturnCallback(function ($data, $type, $format, $context) {
                return $context[AbstractNormalizer::OBJECT_TO_POPULATE] ?? new Lesson();
            });

        $lesson = $this->factory->createFromDTO($dto, $user);

        $this->assertInstanceOf(Lesson::class, $lesson);
        $this->assertSame($user, $lesson->getUser());
    }

    public function testUpdateFromDTOWithWordSynchronization(): void
    {
        $lesson = new Lesson();

        $word1 = $this->createMock(Word::class);
        $word1->method('getId')->willReturn(1);
        $word2 = $this->createMock(Word::class);
        $word2->method('getId')->willReturn(2);

        $lesson->addWord($word1);
        $lesson->addWord($word2);

        $dto = new UpdateLessonDTO(
            123,
            'Updated Title',
            'Description',
            'en',
            [
                ['id' => 1, 'text' => 'Updated'],
                ['text' => 'New Word']
            ]
        );

        $this->serializer->method('denormalize')
            ->willReturnCallback(function ($data, $type, $format, $context) {
                if ($type === Word::class) {
                    return new Word();
                }
                return $context[AbstractNormalizer::OBJECT_TO_POPULATE] ?? null;
            });

        $updatedLesson = $this->factory->updateFromDTO($lesson, $dto);

        $this->assertCount(2, $updatedLesson->getWords());
        $this->assertFalse($updatedLesson->getWords()->contains($word2));
        $this->assertTrue($updatedLesson->getWords()->contains($word1));
    }

    public function testUpdateFromDTOWithoutWords(): void
    {
        $lesson = new Lesson();
        $dto = new UpdateLessonDTO(123, 'Title', 'Desc', 'pl', null);

        $this->serializer->expects($this->once())->method('denormalize');

        $result = $this->factory->updateFromDTO($lesson, $dto);

        $this->assertSame($lesson, $result);
    }
}