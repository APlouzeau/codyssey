<?php

namespace App\Entity;

use App\Repository\TipsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TipsRepository::class)]
class Tips
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    /**
     * @var Collection<int, Enonce>
     */
    #[ORM\ManyToMany(targetEntity: Enonce::class, inversedBy: 'tips')]
    private Collection $enonces;

    public function __construct()
    {
        $this->enonces = new ArrayCollection();
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
     * @return Collection<int, Enonce>
     */
    public function getEnonces(): Collection
    {
        return $this->enonces;
    }

    public function addEnonce(Enonce $enonce): static
    {
        if (!$this->enonces->contains($enonce)) {
            $this->enonces->add($enonce);
        }

        return $this;
    }

    public function removeEnonce(Enonce $enonce): static
    {
        $this->enonces->removeElement($enonce);

        return $this;
    }
}
