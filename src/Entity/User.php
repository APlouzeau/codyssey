<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $XP = 0;

    #[ORM\Column(length: 255)]
    private ?string $pseudo = null;

    /**
     * @var Collection<int, UserLevel>
     */
    #[ORM\OneToMany(targetEntity: UserLevel::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $userLevels;

    /**
     * @var Collection<int, UserSkin>
     */
    #[ORM\OneToMany(targetEntity: UserSkin::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $userSkins;

    public function __construct()
    {
        $this->userLevels = new ArrayCollection();
        $this->userSkins = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getXP(): ?int
    {
        return $this->XP;
    }

    public function setXP(int $XP): static
    {
        $this->XP = $XP;

        return $this;
    }

    /**
     * @return Collection<int, UserLevel>
     */
    public function getUserLevels(): Collection
    {
        return $this->userLevels;
    }

    public function addUserLevel(UserLevel $userLevel): static
    {
        if (!$this->userLevels->contains($userLevel)) {
            $this->userLevels->add($userLevel);
            $userLevel->setUser($this);
        }

        return $this;
    }

    public function removeUserLevel(UserLevel $userLevel): static
    {
        if ($this->userLevels->removeElement($userLevel)) {
            // set the owning side to null (unless already changed)
            if ($userLevel->getUser() === $this) {
                $userLevel->setUser(null);
            }
        }

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * @return Collection<int, UserSkin>
     */
    public function getUserSkins(): Collection
    {
        return $this->userSkins;
    }

    public function addUserSkin(UserSkin $userSkin): static
    {
        if (!$this->userSkins->contains($userSkin)) {
            $this->userSkins->add($userSkin);
            $userSkin->setUser($this);
        }

        return $this;
    }

    public function removeUserSkin(UserSkin $userSkin): static
    {
        if ($this->userSkins->removeElement($userSkin)) {
            // set the owning side to null (unless already changed)
            if ($userSkin->getUser() === $this) {
                $userSkin->setUser(null);
            }
        }

        return $this;
    }
}
