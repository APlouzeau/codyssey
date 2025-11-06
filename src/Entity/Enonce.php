<?php

namespace App\Entity;

use App\Repository\EnonceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EnonceRepository::class)]
class Enonce
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $expected_results = null;

    #[ORM\Column]
    private ?int $xp_gain = null;

    #[ORM\Column]
    private ?int $life_number = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $timer = null;

    /**
     * @var Collection<int, Tips>
     */
    #[ORM\ManyToMany(targetEntity: Tips::class, mappedBy: 'enonces')]
    private Collection $tips;

    /**
     * @var Collection<int, Level>
     */
    #[ORM\OneToMany(targetEntity: Level::class, mappedBy: 'enonce')]
    private Collection $levels;

    public function __construct()
    {
        $this->tips = new ArrayCollection();
        $this->levels = new ArrayCollection();
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

    public function getExpectedResults(): ?string
    {
        return $this->expected_results;
    }

    public function setExpectedResults(string $expected_results): static
    {
        $this->expected_results = $expected_results;

        return $this;
    }

    public function getXpGain(): ?int
    {
        return $this->xp_gain;
    }

    public function setXpGain(int $xp_gain): static
    {
        $this->xp_gain = $xp_gain;

        return $this;
    }

    public function getLifeNumber(): ?int
    {
        return $this->life_number;
    }

    public function setLifeNumber(int $life_number): static
    {
        $this->life_number = $life_number;

        return $this;
    }

    public function getTimer(): ?\DateTimeImmutable
    {
        return $this->timer;
    }

    public function setTimer(\DateTimeImmutable $timer): static
    {
        $this->timer = $timer;

        return $this;
    }

    /**
     * @return Collection<int, Tips>
     */
    public function getTips(): Collection
    {
        return $this->tips;
    }

    public function addTip(Tips $tip): static
    {
        if (!$this->tips->contains($tip)) {
            $this->tips->add($tip);
            $tip->addEnonce($this);
        }

        return $this;
    }

    public function removeTip(Tips $tip): static
    {
        if ($this->tips->removeElement($tip)) {
            $tip->removeEnonce($this);
        }

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
            $level->setEnonce($this);
        }

        return $this;
    }

    public function removeLevel(Level $level): static
    {
        if ($this->levels->removeElement($level)) {
            // set the owning side to null (unless already changed)
            if ($level->getEnonce() === $this) {
                $level->setEnonce(null);
            }
        }

        return $this;
    }
}
