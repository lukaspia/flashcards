<?php

declare(strict_types=1);


namespace App\Service\Lesson;


use App\Entity\Lesson;
use App\Event\AddLessonEvent;
use App\Utils\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LessonServices
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    private ValidatorInterface $validator;
    /**
     * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
     */
    private EventDispatcherInterface $eventDispatcher;
    /**
     * @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private Filesystem $filesystem;
    /**
     * @var \App\Utils\FileManager
     */
    private FileManager $fileManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher,
        ParameterBagInterface $parameterBag,
        Filesystem $filesystem,
        FileManager $fileManager
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
        $this->parameterBag = $parameterBag;
        $this->filesystem = $filesystem;
        $this->fileManager = $fileManager;
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @return \App\Entity\Lesson
     */
    public function addLesson(Lesson $lesson): Lesson
    {
        $lesson = $this->saveLesson($lesson);

        $this->eventDispatcher->dispatch(new AddLessonEvent($lesson), AddLessonEvent::NAME);

        return $lesson;
    }

    public function updateLesson(Lesson $lesson): Lesson
    {
        return $this->saveLesson($lesson);
    }

    /**
     * @param \App\Entity\Lesson $lesson
     * @return \App\Entity\Lesson
     */
    public function removeLesson(Lesson $lesson): Lesson
    {
        $this->entityManager->remove($lesson);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new AddLessonEvent($lesson), AddLessonEvent::NAME);

        return $lesson;
    }

    private function saveLesson(Lesson $lesson): Lesson
    {
        $errors = $this->validator->validate($lesson);

        if (count($errors) > 0) {
            throw new InvalidArgumentException((string)$errors);
        }

        $this->entityManager->persist($lesson);
        $this->entityManager->flush();

        $this->moveWordsImages($lesson);

        return $lesson;
    }

    private function moveWordsImages(Lesson $lesson): void
    {
        if($words = $lesson->getWords()) {
            $tempFiles = $this->parameterBag->get('word_image_upload_dir_temp');
            $wordFiles = $this->parameterBag->get('word_image_upload_dir');

            /**@var \App\Entity\Word $word**/
            foreach ($words as $word) {
                if($image = $word->getImage()) {

                    $extension = pathinfo($tempFiles . $image, PATHINFO_EXTENSION);
                    $newFileName = $word->getId() . '.' . $extension;
                    $wordDirectory = $wordFiles . $lesson->getUser()->getId() . '/' . $lesson->getId() . '/';
                    $wordFilePath = $wordDirectory . $newFileName;

                    $this->fileManager->moveFile($tempFiles . $image, $wordFilePath);
                }
            }
        }
    }
}