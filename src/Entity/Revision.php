<?php

namespace App\Entity;

use App\Repository\RevisionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RevisionRepository::class)]
class Revision
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Flashcard::class, inversedBy: 'revisions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Flashcard $flashcard = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastReview = null; // Date de la dernière révision

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dueDate = null; // Date de la prochaine révision

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $state = null; // État de la révision (1: Learning, 2: Review, 3: Relearning)

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $stability = 1.0; // Stabilité initiale

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $difficulty = 5.0; // Difficulté initiale

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $interval = null; // Intervalle en jours avant la prochaine révision

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $retrievability = 0.9; // Probabilité de rappel

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $rating = null; // Dernière évaluation de l'utilisateur (Again, Hard, Good, Easy)

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $step = null; // Étape de la révision (nécessaire pour l'algorithme FSRS)

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFlashcard(): ?Flashcard
    {
        return $this->flashcard;
    }

    public function setFlashcard(?Flashcard $flashcard): static
    {
        $this->flashcard = $flashcard;

        return $this;
    }

    public function getLastReview(): ?\DateTimeInterface
    {
        return $this->lastReview;
    }

    public function setLastReview(?\DateTimeInterface $lastReview): static
    {
        $this->lastReview = $lastReview;

        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeInterface $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(?int $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getStability(): ?float
    {
        return $this->stability;
    }

    public function setStability(?float $stability): static
    {
        $this->stability = $stability;

        return $this;
    }

    public function getDifficulty(): ?float
    {
        return $this->difficulty;
    }

    public function setDifficulty(?float $difficulty): static
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getInterval(): ?int
    {
        return $this->interval;
    }

    public function setInterval(?int $interval): static
    {
        $this->interval = $interval;

        return $this;
    }

    public function getRetrievability(): ?float
    {
        return $this->retrievability;
    }

    public function setRetrievability(?float $retrievability): static
    {
        $this->retrievability = $retrievability;

        return $this;
    }

    public function getRating(): ?string
    {
        return $this->rating;
    }

    public function setRating(?string $rating): static
    {
        $this->rating = $rating;

        return $this;
    }

    public function getStep(): ?int
    {
        return $this->step;
    }

    public function setStep(?int $step): static
    {
        $this->step = $step;

        return $this;
    }
}
