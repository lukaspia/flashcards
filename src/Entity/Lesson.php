<?php

namespace App\Entity;

use App\Repository\LessonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LessonRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Groups('lesson:read')]
class Lesson
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Lesson name is required")]
    #[Groups('lesson:write')]
    private string $name;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user", referencedColumnName: "id", onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $addDate = null;

    #[ORM\OneToMany(targetEntity: Word::class, mappedBy: 'lesson', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['sequence' => 'ASC'])]
    private ?Collection $words;

    public function __construct()
    {
        $this->words = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getWords(): ?Collection
    {
        return $this->words;
    }

    public function setWords(?Collection $words): void
    {
        $this->words = $words;
    }

    #[ORM\PrePersist]
    public function setPersistAddDate(): void
    {
        if ($this->addDate === null) {
            $this->setAddDate(new \DateTime());
        }
    }

    #[ORM\PreFlush]
    public function updateWordsOrder(): void
    {
        $order = 1;
        /**
         * @var Word $word
         */
        foreach ($this->words as $word) {
            $word->setSequence($order);
            $order++;
        }

        //throw new \Exception('SOMETHING WRONG ' . $word->getSequence());
    }
}
