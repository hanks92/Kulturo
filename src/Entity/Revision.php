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

    #[ORM\ManyToOne(inversedBy: 'revisions')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Flashcard $flashcard = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $reviewDate = null; // Dernière révision (last_review)

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dueDate = null; // Prochaine révision (due)

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $stability = null; // Stabilité, peut rester NULL

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $retrievability = null; // Probabilité de rappel initiale

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $difficulty = null; // Difficulté, peut rester NULL

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $rating = null; // 1 = Again, 2 = Hard, 3 = Good, 4 = Easy

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $state = null; // 1 = Learning, 2 = Review, 3 = Relearning

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $step = null; // Étape actuelle de progression

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastReview = null;

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

    public function getReviewDate(): ?\DateTimeInterface
    {
        return $this->reviewDate;
    }

    public function setReviewDate(?\DateTimeInterface $reviewDate): static
    {
        $this->reviewDate = $reviewDate;
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

    public function getStability(): ?float
    {
        return $this->stability;
    }

    public function setStability(?float $stability): static
    {
        $this->stability = $stability;
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

    public function getDifficulty(): ?float
    {
        return $this->difficulty;
    }

    public function setDifficulty(?float $difficulty): static
    {
        $this->difficulty = $difficulty;
        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): static
    {
        $this->rating = $rating;
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

    public function getStep(): ?int
    {
        return $this->step;
    }

    public function setStep(?int $step): static
    {
        $this->step = $step;
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
}
