<?php

declare(strict_types=1);


namespace App\Controller\Api;


use App\Form\LessonType;
use App\Service\Lesson\LessonServices;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LessonController extends AbstractApiController
{
    /**
     * @var \App\Service\Lesson\LessonServices
     */
    private LessonServices $lessonServices;

    public function __construct(EntityManagerInterface $entityManager, LessonServices $lessonServices)
    {
        parent::__construct($entityManager);
        $this->lessonServices = $lessonServices;
    }

    #[Route('/lesson', name: 'lesson', methods: ['POST'])]
    public function addLesson(Request $request): JsonResponse
    {
        $form = $this->createForm(LessonType::class);
        $form->handleRequest($this->getFormRequest($request));

        if ($form->isSubmitted() && $form->isValid()) {
            $lesson = $form->getData();
            $lesson->setAddDate(new \DateTime());

            try {
                $this->lessonServices->addLesson($lesson);
            } catch (\Exception $ex) {
                return $this->createResponse('Lesson not created', [$ex], 400);
            }

            return $this->createResponse('Lesson created successfully', [], 201);
        }

        return $this->createResponse('Lesson not created', $this->getFormErrors($form), 400);
    }

    private function getFormRequest(Request $request): Request
    {
        if(empty($request->request->all('lesson'))) {
            $request->request->set('lesson', $request->request->all());
        }

        return $request;
    }

    private function getFormErrors($form): array
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }
        return $errors;
    }
}