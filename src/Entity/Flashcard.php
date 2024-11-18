<?php

namespace App\Entity;

use App\Repository\FlashcardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FlashcardRepository::class)]
class Flashcard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $question = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $answer = null;

    #[ORM\ManyToOne(inversedBy: 'flashcards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Deck $deck = null;

    /**
     * @var Collection<int, Revision>
     */
    #[ORM\OneToMany(targetEntity: Revision::class, mappedBy: 'flashcard')]
    private Collection $revisions;

    public function __construct()
    {
        $this->revisions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(?string $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(?string $answer): static
    {
        $this->answer = $answer;

        return $this;
    }

    public function getDeck(): ?Deck
    {
        return $this->deck;
    }

    public function setDeck(?Deck $deck): static
    {
        $this->deck = $deck;

        return $this;
    }

    /**
     * @return Collection<int, Revision>
     */
    public function getRevisions(): Collection
    {
        return $this->revisions;
    }

    public function addRevision(Revision $revision): static
    {
        if (!$this->revisions->contains($revision)) {
            $this->revisions->add($revision);
            $revision->setFlashcard($this);
        }

        return $this;
    }

    public function removeRevision(Revision $revision): static
    {
        if ($this->revisions->removeElement($revision)) {
            // set the owning side to null (unless already changed)
            if ($revision->getFlashcard() === $this) {
                $revision->setFlashcard(null);
            }
        }

        return $this;
    }
}
