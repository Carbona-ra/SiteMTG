<?php

namespace App\Entity;

use App\Repository\CardListRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardListRepository::class)]
class CardList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'AddTo')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Deck $addTo = null;

    public function __toString()
    {
      return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAddTo(): ?Deck
    {
        return $this->addTo;
    }

    public function setAddTo(?Deck $addTo): static
    {
        $this->addTo = $addTo;

        return $this;
    }
}
