<?php

namespace App\Entity;

use App\Repository\LessonRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LessonRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Lesson
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "Lesson name is required")]
    private string $name;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user", referencedColumnName: "id", onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $addDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddDate(): ?\DateTime
    {
        return $this->addDate;
    }

    public function setAddDate(?\DateTime $addDate): void
    {
        $this->addDate = $addDate;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    #[ORM\PrePersist]
    public function setPersistAddDate(): void
    {
        if ($this->addDate === null) {
            $this->setAddDate(new \DateTime());
        }
    }
}
