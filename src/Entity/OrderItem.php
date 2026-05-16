<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
private ?Orders $order = null;

#[ORM\ManyToOne]
private ?Product $product = null;

#[ORM\Column]
private int $quantity;

#[ORM\Column]
private float $price;

#[ORM\Column(length: 255)]
private ?string $productName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Orders { return $this->order; }
public function setOrder(?Orders $order): static { $this->order = $order; return $this; }

public function getProduct(): ?Product { return $this->product; }
public function setProduct(?Product $product): static { $this->product = $product; return $this; }

public function getQuantity(): int { return $this->quantity; }
public function setQuantity(int $q): static { $this->quantity = $q; return $this; }

public function getPrice(): float { return $this->price; }
public function setPrice(float $p): static { $this->price = $p; return $this; }

public function getProductName(): ?string { return $this->productName; }
public function setProductName(string $p): static { $this->productName = $p; return $this; }
}
