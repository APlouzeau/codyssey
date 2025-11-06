<?php

namespace App\Entity;

use App\Repository\LevelRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LevelRepository::class)]
class Level
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $number = null;

    #[ORM\ManyToOne(inversedBy: 'levels')]
    private ?Avatar $avatar = null;

    #[ORM\ManyToOne(inversedBy: 'levels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    #[ORM\ManyToOne(inversedBy: 'levels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Enonce $enonce = null;

    #[ORM\ManyToOne(inversedBy: 'levels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LevelType $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getAvatar(): ?Avatar
    {
        return $this->avatar;
    }

    public function setAvatar(?Avatar $avatar): static
    {
        $this->avatar = $avatar;

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

    public function getEnonce(): ?Enonce
    {
        return $this->enonce;
    }

    public function setEnonce(?Enonce $enonce): static
    {
        $this->enonce = $enonce;

        return $this;
    }

    public function getType(): ?LevelType
    {
        return $this->type;
    }

    public function setType(?LevelType $type): static
    {
        $this->type = $type;

        return $this;
    }
}
