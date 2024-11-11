<?php

namespace App\Entity;

use App\Repository\DeckRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeckRepository::class)]
class Deck
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'decks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    /**
     * @var Collection<int, Flashcard>
     */
    #[ORM\OneToMany(targetEntity: Flashcard::class, mappedBy: 'deck')]
    private Collection $flashcards;

    /**
     * @var Collection<int, RevisionSession>
     */
    #[ORM\OneToMany(targetEntity: RevisionSession::class, mappedBy: 'deck')]
    private Collection $revisionSessions;

    public function __construct()
    {
        $this->flashcards = new ArrayCollection();
        $this->revisionSessions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, Flashcard>
     */
    public function getFlashcards(): Collection
    {
        return $this->flashcards;
    }

    public function addFlashcard(Flashcard $flashcard): static
    {
        if (!$this->flashcards->contains($flashcard)) {
            $this->flashcards->add($flashcard);
            $flashcard->setDeck($this);
        }

        return $this;
    }

    public function removeFlashcard(Flashcard $flashcard): static
    {
        if ($this->flashcards->removeElement($flashcard)) {
            // set the owning side to null (unless already changed)
            if ($flashcard->getDeck() === $this) {
                $flashcard->setDeck(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RevisionSession>
     */
    public function getRevisionSessions(): Collection
    {
        return $this->revisionSessions;
    }

    public function addRevisionSession(RevisionSession $revisionSession): static
    {
        if (!$this->revisionSessions->contains($revisionSession)) {
            $this->revisionSessions->add($revisionSession);
            $revisionSession->setDeck($this);
        }

        return $this;
    }

    public function removeRevisionSession(RevisionSession $revisionSession): static
    {
        if ($this->revisionSessions->removeElement($revisionSession)) {
            // set the owning side to null (unless already changed)
            if ($revisionSession->getDeck() === $this) {
                $revisionSession->setDeck(null);
            }
        }

        return $this;
    }
}
