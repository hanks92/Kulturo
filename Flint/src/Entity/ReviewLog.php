<?php

namespace App\Entity;

use App\Repository\ReviewLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewLogRepository::class)]
class ReviewLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $rating = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $reviewDateTime = null;

    #[ORM\Column(nullable: true)]
    private ?int $reviewDuration = null;

    #[ORM\ManyToOne(inversedBy: 'reviewLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Revision $revision = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;

        return $this;
    }

    public function getReviewDateTime(): ?\DateTimeInterface
    {
        return $this->reviewDateTime;
    }

    public function setReviewDateTime(\DateTimeInterface $reviewDateTime): static
    {
        $this->reviewDateTime = $reviewDateTime;

        return $this;
    }

    public function getReviewDuration(): ?int
    {
        return $this->reviewDuration;
    }

    public function setReviewDuration(?int $reviewDuration): static
    {
        $this->reviewDuration = $reviewDuration;

        return $this;
    }

    public function getRevision(): ?Revision
    {
        return $this->revision;
    }

    public function setRevision(?Revision $revision): static
    {
        $this->revision = $revision;

        return $this;
    }
}
