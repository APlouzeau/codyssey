<?php

namespace App\Entity;

use App\Repository\LessonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LessonRepository::class)]
class Lesson
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    /**
     * @var Collection<int, LessonCodePart>
     */
    #[ORM\OneToMany(targetEntity: LessonCodePart::class, mappedBy: 'lesson')]
    private Collection $lessonCodeParts;

    #[ORM\ManyToOne(inversedBy: 'lessons')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    public function __construct()
    {
        $this->lessonCodeParts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return Collection<int, LessonCodePart>
     */
    public function getLessonCodeParts(): Collection
    {
        return $this->lessonCodeParts;
    }

    public function addLessonCodePart(LessonCodePart $lessonCodePart): static
    {
        if (!$this->lessonCodeParts->contains($lessonCodePart)) {
            $this->lessonCodeParts->add($lessonCodePart);
            $lessonCodePart->setLesson($this);
        }

        return $this;
    }

    public function removeLessonCodePart(LessonCodePart $lessonCodePart): static
    {
        if ($this->lessonCodeParts->removeElement($lessonCodePart)) {
            // set the owning side to null (unless already changed)
            if ($lessonCodePart->getLesson() === $this) {
                $lessonCodePart->setLesson(null);
            }
        }

        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): static
    {
        $this->language = $language;

        return $this;
    }
}
