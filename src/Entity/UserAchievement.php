<?php

namespace App\Entity;

use App\Repository\UserAchievementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserAchievementRepository::class)]
class UserAchievement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userAchievements')]
    private ?user $appUser = null;

    #[ORM\ManyToOne]
    private ?Achievement $achievement = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $achievedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppUser(): ?user
    {
        return $this->appUser;
    }

    public function setAppUser(?user $appUser): static
    {
        $this->appUser = $appUser;

        return $this;
    }

    public function getAchievement(): ?Achievement
    {
        return $this->achievement;
    }

    public function setAchievement(?Achievement $achievement): static
    {
        $this->achievement = $achievement;

        return $this;
    }

    public function getAchievedAt(): ?\DateTimeImmutable
    {
        return $this->achievedAt;
    }

    public function setAchievedAt(?\DateTimeImmutable $achievedAt): static
    {
        $this->achievedAt = $achievedAt;

        return $this;
    }
}
