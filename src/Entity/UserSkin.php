<?php

namespace App\Entity;

use App\Repository\UserSkinRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserSkinRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_user_skin', columns: ['user_id', 'skin_id'])]
class UserSkin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userSkins')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Skin $skin = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $unlocked = false;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isCurrent = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getSkin(): ?Skin
    {
        return $this->skin;
    }

    public function setSkin(?Skin $skin): static
    {
        $this->skin = $skin;

        return $this;
    }

    public function isUnlocked(): ?bool
    {
        return $this->unlocked;
    }

    public function setUnlocked(bool $unlocked): static
    {
        $this->unlocked = $unlocked;

        return $this;
    }

    public function isCurrent(): ?bool
    {
        return $this->isCurrent;
    }

    public function setIsCurrent(bool $isCurrent): static
    {
        $this->isCurrent = $isCurrent;

        return $this;
    }
}
