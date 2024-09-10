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

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(length: 255)]
    private ?string $commanderName = null;

    /**
     * @var Collection<int, Card>
     */
    #[ORM\OneToMany(targetEntity: Card::class, cascade: ['remove'], mappedBy: 'addTo', orphanRemoval: true)]
    private Collection $AddTo;

    #[ORM\ManyToOne(inversedBy: 'decks')]
    private ?User $Creator = null;


    public function __construct()
    {
        $this->AddTo = new ArrayCollection();
    }
    
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

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
    }

    

    public function getCommanderName(): ?string
    {
        return $this->commanderName;
    }

    public function setCommanderName(string $commanderName): static
    {
        $this->commanderName = $commanderName;

        return $this;
    }

    /**
     * @return Collection<int, Card>
     */
    public function getAddTo(): Collection
    {
        return $this->AddTo;
    }

    public function addAddTo(Card $addTo): static
    {
        if (!$this->AddTo->contains($addTo)) {
            $this->AddTo->add($addTo);
            $addTo->setAddTo($this);
        }

        return $this;
    }

    public function removeAddTo(Card $addTo): static
    {
        if ($this->AddTo->removeElement($addTo)) {
            // set the owning side to null (unless already changed)
            if ($addTo->getAddTo() === $this) {
                $addTo->setAddTo(null);
            }
        }

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->Creator;
    }

    public function setCreator(?User $Creator): static
    {
        $this->Creator = $Creator;

        return $this;
    }

    
}
