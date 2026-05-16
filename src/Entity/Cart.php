<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
private ?User $user = null;

#[ORM\ManyToOne]
private ?Product $product = null;

#[ORM\Column]
private int $quantity = 1;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getUser(): ?User { return $this->user; }
public function setUser(?User $user): static { $this->user = $user; return $this; }

public function getProduct(): ?Product { return $this->product; }
public function setProduct(?Product $product): static { $this->product = $product; return $this; }

public function getQuantity(): int { return $this->quantity; }
public function setQuantity(int $q): static { $this->quantity = $q; return $this; }
}
