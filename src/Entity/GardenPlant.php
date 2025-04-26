<?php

namespace App\Entity;

use App\Repository\GardenPlantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GardenPlantRepository::class)]
class GardenPlant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?User $userApp = null;

    #[ORM\Column(nullable: true)]
    private ?int $x = null;

    #[ORM\Column(nullable: true)]
    private ?int $y = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(nullable: true)]
    private ?int $level = null;

    #[ORM\Column(nullable: true)]
    private ?int $waterReceived = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserApp(): ?User
    {
        return $this->userApp;
    }

    public function setUserApp(?User $userApp): static
    {
        $this->userApp = $userApp;

        return $this;
    }

    public function getX(): ?int
    {
        return $this->x;
    }

    public function setX(?int $x): static
    {
        $this->x = $x;

        return $this;
    }

    public function getY(): ?int
    {
        return $this->y;
    }

    public function setY(?int $y): static
    {
        $this->y = $y;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getWaterReceived(): ?int
    {
        return $this->waterReceived;
    }

    public function setWaterReceived(?int $waterReceived): static
    {
        $this->waterReceived = $waterReceived;

        return $this;
    }
}
