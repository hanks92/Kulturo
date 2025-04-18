<?php

namespace App\Entity;

use App\Repository\UserStatsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserStatsRepository::class)]
class UserStats
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'stats', targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?int $streak = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxStreak = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastActivity = null;

    #[ORM\Column(nullable: true)]
    private ?int $totalXp = null;

    #[ORM\Column(nullable: true)]
    private ?int $cardsReviewed = null;

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

    public function getStreak(): ?int
    {
        return $this->streak;
    }

    public function setStreak(?int $streak): static
    {
        $this->streak = $streak;
        
        return $this;
    }

    public function getMaxStreak(): ?int
    {
        return $this->maxStreak;
    }

    public function setMaxStreak(?int $maxStreak): static
    {
        $this->maxStreak = $maxStreak;

        return $this;
    }

    public function getLastActivity(): ?\DateTimeImmutable
    {
        return $this->lastActivity;
    }

    public function setLastActivity(?\DateTimeImmutable $lastActivity): static
    {
        $this->lastActivity = $lastActivity;

        return $this;
    }

    public function getTotalXp(): ?int
    {
        return $this->totalXp;
    }

    public function setTotalXp(?int $totalXp): static
    {
        $this->totalXp = $totalXp;

        return $this;
    }

    public function getCardsReviewed(): ?int
    {
        return $this->cardsReviewed;
    }

    public function setCardsReviewed(?int $cardsReviewed): static
    {
        $this->cardsReviewed = $cardsReviewed;

        return $this;
    }
}
