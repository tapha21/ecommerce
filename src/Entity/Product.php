<?php

namespace App\Entity;

use App\Entity\ProductLot;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 255)]
private ?string $titre = null;

#[ORM\Column(type: 'text')]
private ?string $description = null;

#[ORM\Column]
private ?float $prix = null;

#[ORM\Column]
private ?int $stock = null;

#[ORM\Column(length: 255)]
private ?string $image = null;

#[ORM\Column]
private bool $nouveaute = false;
#[ORM\Column]
private bool $phares = false;

#[ORM\Column]
private bool $promotion = false;

#[ORM\Column]
private bool $bestSeller = false;

#[ORM\ManyToOne]
private ?Category $category = null;
#[ORM\Column]
private \DateTimeImmutable $createdAt;

#[ORM\Column(nullable: true)]
private ?\DateTimeImmutable $updatedAt = null;

#[ORM\OneToMany(
    mappedBy: 'product',
    targetEntity: ProductLot::class,
    cascade: ['persist', 'remove'],
    orphanRemoval: true
)]
private Collection $lots;

public function __construct()
{
    $this->lots = new ArrayCollection();
    $this->createdAt = new \DateTimeImmutable();
}
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getTitre(): ?string { return $this->titre; }
public function setTitre(string $titre): static { $this->titre = $titre; return $this; }

public function getDescription(): ?string { return $this->description; }
public function setDescription(string $description): static { $this->description = $description; return $this; }

public function getPrix(): ?float { return $this->prix; }
public function setPrix(float $prix): static { $this->prix = $prix; return $this; }

public function getStock(): ?int { return $this->stock; }
public function setStock(int $stock): static { $this->stock = $stock; return $this; }

public function getImage(): ?string { return $this->image; }
public function setImage(string $image): static { $this->image = $image; return $this; }

public function isNouveaute(): bool { return $this->nouveaute; }
public function setNouveaute(bool $n): static { $this->nouveaute = $n; return $this; }

public function isPromotion(): bool { return $this->promotion; }
public function setPromotion(bool $p): static { $this->promotion = $p; return $this; }

public function isBestSeller(): bool { return $this->bestSeller; }
public function setBestSeller(bool $b): static { $this->bestSeller = $b; return $this; }

public function getCategory(): ?Category { return $this->category; }
public function setCategory(?Category $category): static { $this->category = $category; return $this; }

public function isPhares(): bool { return $this->phares; }
public function setPhares(bool $p): static { $this->phares = $p; return $this; }

public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }

public function getLots(): Collection
{
    return $this->lots;
}
public function setLots(Collection $lots): static
{
    $this->lots = $lots;

    foreach ($this->lots as $lot) {
        $lot->setProduct($this);
    }

    return $this;
}
public function addLot(ProductLot $lot): static
{
    if (!$this->lots->contains($lot)) {
        $this->lots->add($lot);
        $lot->setProduct($this);
    }

    return $this;
}

public function removeLot(ProductLot $lot): static
{
    if ($this->lots->removeElement($lot)) {
        if ($lot->getProduct() === $this) {
            $lot->setProduct(null);
        }
    }

    return $this;
}
}
