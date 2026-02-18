<?php

declare(strict_types=1);


namespace App\Factory;


use App\DTO\AddLessonDTO;
use App\Entity\Lesson;
use App\Entity\Word;
use App\Factory\LessonFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class LessonFactory implements LessonFactoryInterface
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @param \App\DTO\AddLessonDTO $dto
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @return \App\Entity\Lesson
     */
    public function createFromDTO(AddLessonDTO $dto, UserInterface $user): Lesson
    {
        $lesson = new Lesson();
        $lesson->setName($dto->name);
        $lesson->setSourceLanguage($dto->sourceLanguage);
        $lesson->setTargetLanguage($dto->targetLanguage);
        $lesson->setUser($user);

        return $lesson;
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @param array $data
     * @return \App\Entity\Lesson
     */
    public function updateFromRequestData(Lesson $lesson, array $data): Lesson
    {
        if (empty($data)) {
            return $lesson;
        }

        try {
            $this->updateLessonWords($lesson, $data);

            $updatedLesson = $this->serializer->denormalize($data, Lesson::class, null, [
                AbstractNormalizer::OBJECT_TO_POPULATE => $lesson,
                AbstractNormalizer::GROUPS => [Lesson::LESSON_WRITE_GROUP],
            ]);

            $this->validateLesson($updatedLesson);

            return $updatedLesson;
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to update lesson: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @return void
     */
    private function validateLesson(Lesson $lesson): void
    {
        $errors = $this->validator->validate($lesson);
        if ($errors->count() > 0) {
            throw new \RuntimeException('Validation failed: ' . $errors);
        }
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @param array $data
     * @return void
     */
    private function updateLessonWords(Lesson $lesson, array $data): void
    {
        if (isset($data['words'])) {
            $words = $lesson->getWords();
            $words->clear();

            $lessonWords = $this->entityManager->getRepository(Word::class)->findByLessonId($lesson->getId());

            foreach ($data['words'] as $wordData) {
                $wordEntity = $this->createWordEntity($wordData, $lessonWords);
                $wordEntity->setLesson($lesson);
                $words->add($wordEntity);
            }
        }
    }

    /**
     * @param array $wordData
     * @param array $lessonWords
     * @return \App\Entity\Word
     */
    private function createWordEntity(array $wordData, array $lessonWords): Word
    {
        $context = [];
        if (isset($wordData['id'], $lessonWords[$wordData['id']])) {
            $context = [AbstractNormalizer::OBJECT_TO_POPULATE => $lessonWords[$wordData['id']]];
        }

        return $this->serializer->denormalize($wordData, Word::class, null, $context);
    }
}