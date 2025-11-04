<?php

namespace App\Entity;

use App\Repository\AvatarRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvatarRepository::class)]
class Avatar
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Level>
     */
    #[ORM\OneToMany(targetEntity: Level::class, mappedBy: 'avatar')]
    private Collection $levels;

    /**
     * @var Collection<int, Skin>
     */
    #[ORM\OneToMany(targetEntity: Skin::class, mappedBy: 'avatar', orphanRemoval: true)]
    private Collection $skins;

    public function __construct()
    {
        $this->levels = new ArrayCollection();
        $this->skins = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Level>
     */
    public function getLevels(): Collection
    {
        return $this->levels;
    }

    public function addLevel(Level $level): static
    {
        if (!$this->levels->contains($level)) {
            $this->levels->add($level);
            $level->setAvatar($this);
        }

        return $this;
    }

    public function removeLevel(Level $level): static
    {
        if ($this->levels->removeElement($level)) {
            // set the owning side to null (unless already changed)
            if ($level->getAvatar() === $this) {
                $level->setAvatar(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Skin>
     */
    public function getSkins(): Collection
    {
        return $this->skins;
    }

    public function addSkin(Skin $skin): static
    {
        if (!$this->skins->contains($skin)) {
            $this->skins->add($skin);
            $skin->setAvatar($this);
        }

        return $this;
    }

    public function removeSkin(Skin $skin): static
    {
        if ($this->skins->removeElement($skin)) {
            // set the owning side to null (unless already changed)
            if ($skin->getAvatar() === $this) {
                $skin->setAvatar(null);
            }
        }

        return $this;
    }
}
