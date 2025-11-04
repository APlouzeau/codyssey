<?php

namespace App\Entity;

use App\Repository\SkinRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SkinRepository::class)]
class Skin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $file_name = null;

    #[ORM\Column]
    private ?bool $unlocked_skin = null;

    #[ORM\ManyToOne(inversedBy: 'skins')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Avatar $avatar = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->file_name;
    }

    public function setFileName(string $file_name): static
    {
        $this->file_name = $file_name;

        return $this;
    }

    public function isUnlockedSkin(): ?bool
    {
        return $this->unlocked_skin;
    }

    public function setUnlockedSkin(bool $unlocked_skin): static
    {
        $this->unlocked_skin = $unlocked_skin;

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
}
