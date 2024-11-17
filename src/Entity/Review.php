<?php

namespace Rhys\ReviewBundle\Entity;

use Rhys\ReviewBundle\Repository\ReviewRepository;
use App\Entity\Book;
use App\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $content = null;

    #[ORM\Column]
    private ?int $rating = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Book $book = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'boolean')]
    private $approved = false; 

    #[ORM\Column(type: 'boolean')]
    private bool $contains_spoilers = false;

    #[ORM\OneToMany(mappedBy: 'review', targetEntity: Vote::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $votes;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        if ($rating < 0 || $rating > 10) {
            throw new \InvalidArgumentException('Rating must be between 0 and 10.');
        }
        $this->rating = $rating;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): static
    {
        $this->book = $book;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function isApproved(): bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): self
    {
        $this->approved = $approved;
        return $this;
    }

    public function isContainsSpoilers(): bool
    {
        return $this->contains_spoilers;
    }

    public function setContainsSpoilers(bool $contains_spoilers): self
    {
        $this->contains_spoilers = $contains_spoilers;
        return $this;
    }

    /**
     * @return Collection<int, Vote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): self
    {
        if (!$this->votes->contains($vote)) {
            $this->votes[] = $vote;
            $vote->setReview($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): self
    {
        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getReview() === $this) {
                $vote->setReview(null);
            }
        }

        return $this;
    }
    
    public function getUpvotesCount(): int
    {
        return $this->votes->filter(function (Vote $vote) {
            return $vote->getType() === 'upvote';
        })->count();
    }

    public function getDownvotesCount(): int
    {
        return $this->votes->filter(function (Vote $vote) {
            return $vote->getType() === 'downvote';
        })->count();
    }
}
