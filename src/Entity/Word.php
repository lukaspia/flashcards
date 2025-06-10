<?php

namespace App\Entity;

use App\Repository\WordRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: WordRepository::class)]
#[Groups('lesson:read')]
class Word
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private string $basicWord;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private string $translation;

    #[ORM\Column(type: "text", nullable: true)]
    private string $example;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $image;

    #[ORM\ManyToOne(targetEntity: Lesson::class, inversedBy: "words")]
    #[ORM\JoinColumn(name: "lesson", referencedColumnName: "id")]
    private Lesson $lesson;

    #[ORM\Column(type: "integer")]
    private int $order;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBasicWord(): string
    {
        return $this->basicWord;
    }

    public function setBasicWord(string $basicWord): void
    {
        $this->basicWord = $basicWord;
    }

    public function getTranslation(): string
    {
        return $this->translation;
    }

    public function setTranslation(string $translation): void
    {
        $this->translation = $translation;
    }

    public function getExample(): string
    {
        return $this->example;
    }

    public function setExample(string $example): void
    {
        $this->example = $example;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getLesson(): Lesson
    {
        return $this->lesson;
    }

    public function setLesson(Lesson $lesson): void
    {
        $this->lesson = $lesson;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): void
    {
        $this->order = $order;
    }

    public function getImageRelativePath(): ?string
    {
        return $this->getLesson()->getUser()->getId() . '/' . $this->getLesson()->getId() . '/';
    }
}
