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
#[ORM\Table(name: '`user`')]
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

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profileImage = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $theme = 'light'; // Valeur par défaut

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserStats::class, cascade: ['persist', 'remove'])]
    private ?UserStats $stats = null;

    /**
     * @var Collection<int, Deck>
     */
    #[ORM\OneToMany(targetEntity: Deck::class, mappedBy: 'owner')]
    private Collection $decks;

    /**
     * @var Collection<int, UserAchievement>
     */
    #[ORM\OneToMany(targetEntity: UserAchievement::class, mappedBy: 'appUser')]
    private Collection $userAchievements;

    /**
     * @var Collection<int, GardenPlant>
     */
    #[ORM\OneToMany(targetEntity: GardenPlant::class, mappedBy: 'userApp')]
    private Collection $no;

    public function __construct()
    {
        $this->roles = ['ROLE_USER']; // Rôle par défaut
        $this->decks = new ArrayCollection();
        $this->userAchievements = new ArrayCollection();
        $this->no = new ArrayCollection();
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
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Guarantee every user has at least ROLE_USER
        if (!in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        // Ensure ROLE_USER is always included
        if (!in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }
        $this->roles = array_unique($roles);

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
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getProfileImage(): ?string
    {
        return $this->profileImage;
    }

    public function setProfileImage(?string $profileImage): static
    {
        $this->profileImage = $profileImage;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(?string $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * @return Collection<int, Deck>
     */
    public function getDecks(): Collection
    {
        return $this->decks;
    }

    public function addDeck(Deck $deck): static
    {
        if (!$this->decks->contains($deck)) {
            $this->decks->add($deck);
            $deck->setOwner($this);
        }

        return $this;
    }

    public function removeDeck(Deck $deck): static
    {
        if ($this->decks->removeElement($deck)) {
            // set the owning side to null (unless already changed)
            if ($deck->getOwner() === $this) {
                $deck->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserAchievement>
     */
    public function getUserAchievements(): Collection
    {
        return $this->userAchievements;
    }

    public function addUserAchievement(UserAchievement $userAchievement): static
    {
        if (!$this->userAchievements->contains($userAchievement)) {
            $this->userAchievements->add($userAchievement);
            $userAchievement->setAppUser($this);
        }

        return $this;
    }

    public function removeUserAchievement(UserAchievement $userAchievement): static
    {
        if ($this->userAchievements->removeElement($userAchievement)) {
            // set the owning side to null (unless already changed)
            if ($userAchievement->getAppUser() === $this) {
                $userAchievement->setAppUser(null);
            }
        }

        return $this;
    }

    public function getStats(): ?UserStats
    {
        return $this->stats;
    }

    public function setStats(?UserStats $stats): static
    {
        $this->stats = $stats;
        return $this;
    }

    /**
     * @return Collection<int, GardenPlant>
     */
    public function getNo(): Collection
    {
        return $this->no;
    }

    public function addNo(GardenPlant $no): static
    {
        if (!$this->no->contains($no)) {
            $this->no->add($no);
            $no->setUserApp($this);
        }

        return $this;
    }

    public function removeNo(GardenPlant $no): static
    {
        if ($this->no->removeElement($no)) {
            // set the owning side to null (unless already changed)
            if ($no->getUserApp() === $this) {
                $no->setUserApp(null);
            }
        }

        return $this;
    }

}
