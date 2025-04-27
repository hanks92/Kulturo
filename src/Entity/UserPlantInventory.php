<?php

namespace App\Entity;

use App\Repository\UserPlantInventoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPlantInventoryRepository::class)]
class UserPlantInventory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?user $userApp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $plantType = null;

    #[ORM\Column(nullable: true)]
    private ?int $quantity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserApp(): ?user
    {
        return $this->userApp;
    }

    public function setUserApp(?user $userApp): static
    {
        $this->userApp = $userApp;

        return $this;
    }

    public function getPlantType(): ?string
    {
        return $this->plantType;
    }

    public function setPlantType(?string $plantType): static
    {
        $this->plantType = $plantType;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }
}
