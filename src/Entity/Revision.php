<?php

namespace App\Entity;

use App\Repository\RevisionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    private ?\DateTimeInterface $dueDate = null; // Prochaine révision (due)

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $stability = null; // Stabilité, peut rester NULL

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $difficulty = null; // Difficulté, peut rester NULL

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $state = null; // 1 = Learning, 2 = Review, 3 = Relearning

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $step = null; // Étape actuelle de progression

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastReview = null;

    /**
     * @var Collection<int, ReviewLog>
     */
    #[ORM\OneToMany(targetEntity: ReviewLog::class, mappedBy: 'revision', orphanRemoval: true)]
    private Collection $reviewLogs;

    public function __construct()
    {
        $this->reviewLogs = new ArrayCollection();
    }

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

    public function getDifficulty(): ?float
    {
        return $this->difficulty;
    }

    public function setDifficulty(?float $difficulty): static
    {
        $this->difficulty = $difficulty;
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

    /**
     * @return Collection<int, ReviewLog>
     */
    public function getReviewLogs(): Collection
    {
        return $this->reviewLogs;
    }

    public function addReviewLog(ReviewLog $reviewLog): static
    {
        if (!$this->reviewLogs->contains($reviewLog)) {
            $this->reviewLogs->add($reviewLog);
            $reviewLog->setRevision($this);
        }

        return $this;
    }

    public function removeReviewLog(ReviewLog $reviewLog): static
    {
        if ($this->reviewLogs->removeElement($reviewLog)) {
            // set the owning side to null (unless already changed)
            if ($reviewLog->getRevision() === $this) {
                $reviewLog->setRevision(null);
            }
        }

        return $this;
    }
}
