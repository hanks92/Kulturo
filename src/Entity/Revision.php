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
    private ?Flashcard $flashcard = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $reviewDate = null;

    #[ORM\Column(nullable: true)]
    private ?int $interval = null;

    #[ORM\Column(nullable: true)]
    private ?float $easeFactor = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

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

    public function getInterval(): ?int
    {
        return $this->interval;
    }

    public function setInterval(?int $interval): static
    {
        $this->interval = $interval;

        return $this;
    }

    public function getEaseFactor(): ?float
    {
        return $this->easeFactor;
    }

    public function setEaseFactor(?float $easeFactor): static
    {
        $this->easeFactor = $easeFactor;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
