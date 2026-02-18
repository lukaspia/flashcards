<?php

declare(strict_types=1);


namespace App\Factory;


use App\DTO\AddLessonDTO;
use App\DTO\UpdateLessonDTO;
use App\Entity\Lesson;
use App\Entity\Word;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
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
    public function updateFromDTO(Lesson $lesson, UpdateLessonDTO $dto): Lesson
    {
        $lesson->setName($dto->name);
        $lesson->setSourceLanguage($dto->sourceLanguage);
        $lesson->setTargetLanguage($dto->targetLanguage);

        if ($dto->words !== null) {
            $this->updateLessonWords($lesson, $dto->words);
        }

        return $lesson;
    }

    private function updateLessonWords(Lesson $lesson, array $wordsData): void
    {
        $currentWords = $lesson->getWords();
        $currentWords->clear();

        foreach ($wordsData as $wordData) {
            $word = $this->serializer->denormalize($wordData, Word::class);
            $word->setLesson($lesson);
            $currentWords->add($word);
        }
    }
}