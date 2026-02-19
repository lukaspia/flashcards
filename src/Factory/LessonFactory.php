<?php

declare(strict_types=1);


namespace App\Factory;


use App\DTO\AddLessonDTO;
use App\DTO\UpdateLessonDTO;
use App\Entity\Lesson;
use App\Entity\Word;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 *
 */
readonly class LessonFactory implements LessonFactoryInterface
{
    /**
     * @param \Symfony\Component\Serializer\SerializerInterface $serializer
     */
    public function __construct(
        private SerializerInterface $serializer
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

        $this->serializer->denormalize($dto, Lesson::class, null, [
            AbstractNormalizer::OBJECT_TO_POPULATE => $lesson,
            AbstractNormalizer::GROUPS => ['lesson:write']
        ]);

        $lesson->setUser($user);

        return $lesson;
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @param \App\DTO\UpdateLessonDTO $dto
     * @return \App\Entity\Lesson
     */
    public function updateFromDTO(Lesson $lesson, UpdateLessonDTO $dto): Lesson
    {
        $this->serializer->denormalize($dto, Lesson::class, null, [
            AbstractNormalizer::OBJECT_TO_POPULATE => $lesson,
            AbstractNormalizer::GROUPS => ['lesson:write']
        ]);

        if ($dto->words !== null) {
            $this->updateLessonWords($lesson, $dto->words);
        }

        return $lesson;
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @param array $wordsData
     * @return void
     */
    private function updateLessonWords(Lesson $lesson, array $wordsData): void
    {
        $existingWords = $this->indexWordsById($lesson->getWords());
        $processedIds = [];

        foreach ($wordsData as $data) {
            $wordId = $data['id'] ?? null;

            if ($wordId && isset($existingWords[$wordId])) {
                $this->updateExistingWord($existingWords[$wordId], $data);
                $processedIds[] = $wordId;
            } else {
                $this->addNewWordToLesson($lesson, $data);
            }
        }

        $this->removeOrphanedWords($lesson, $existingWords, $processedIds);
    }

    /**
     * @param iterable $words
     * @return array
     */
    private function indexWordsById(iterable $words): array
    {
        $map = [];
        foreach ($words as $word) {
            if ($word->getId()) {
                $map[$word->getId()] = $word;
            }
        }
        return $map;
    }

    /**
     * @param \App\Entity\Word $word
     * @param array $data
     * @return void
     */
    private function updateExistingWord(Word $word, array $data): void
    {
        $this->serializer->denormalize($data, Word::class, null, [
            AbstractNormalizer::OBJECT_TO_POPULATE => $word,
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['lesson']
        ]);
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @param array $data
     * @return void
     */
    private function addNewWordToLesson(Lesson $lesson, array $data): void
    {
        $newWord = $this->serializer->denormalize($data, Word::class, null, [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['lesson']
        ]);
        $lesson->addWord($newWord);
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @param array $existingWords
     * @param array $processedIds
     * @return void
     */
    private function removeOrphanedWords(Lesson $lesson, array $existingWords, array $processedIds): void
    {
        foreach ($existingWords as $id => $word) {
            if (!in_array($id, $processedIds, true)) {
                $lesson->removeWord($word);
            }
        }
    }
}