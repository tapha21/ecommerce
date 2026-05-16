<?php

namespace App\Entity;

use App\Enum\OrderStatus;
use App\Enum\PaymentMethod;
use App\Repository\OrdersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrdersRepository::class)]
class Orders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

#[ORM\Column]
private ?float $total = null;

#[ORM\Column(enumType: OrderStatus::class)]
private OrderStatus $status = OrderStatus::En_Attente;
#[ORM\Column(length: 50, enumType: PaymentMethod::class)]
private PaymentMethod $paymentMethod = PaymentMethod::CashOnDelivery;
#[ORM\Column(length: 100, nullable: true)]
private ?string $guestName = null;

#[ORM\Column(length: 30, nullable: true)]
private ?string $guestPhone = null;

#[ORM\Column(length: 255, nullable: true)]
private ?string $guestAddress = null;

#[ORM\Column]
private ?\DateTimeImmutable $createdAt = null;
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $trackingNumber = null;
    #[ORM\OneToMany(mappedBy: "order", targetEntity: OrderItem::class, cascade: ["persist"])]
    private Collection $items;

    public function __construct()
{
    $this->items = new ArrayCollection();
}

/** @return Collection<int, OrderItem> */
public function getItems(): Collection
{
    return $this->items;
}

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getStatus(): OrderStatus
{
    return $this->status;
}

public function setStatus(OrderStatus $status): static
{
    $this->status = $status;
    return $this;
}

    public function getUser(): ?User { return $this->user; }
public function setUser(?User $user): static { $this->user = $user; return $this; }

public function getTotal(): ?float { return $this->total; }
public function setTotal(float $t): static { $this->total = $t; return $this; }

public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
public function setCreatedAt(\DateTimeImmutable $d): static { $this->createdAt = $d; return $this; }
public function getTrackingNumber(): ?string { return $this->trackingNumber; }
public function setTrackingNumber(?string $t): static { $this->trackingNumber = $t; return $this; }
public function getPaymentMethod(): PaymentMethod
{
    return $this->paymentMethod;
}

public function setPaymentMethod(PaymentMethod $m): static
{
    $this->paymentMethod = $m;
    return $this;
}
public function getGuestName(): ?string
{
    return $this->guestName;
}
public function setGuestName(?string $n): static
{
    $this->guestName = $n;
    return $this;
}
public function getGuestPhone(): ?string
{
    return $this->guestPhone;
}
public function setGuestPhone(?string $p): static
{
    $this->guestPhone = $p;
    return $this;
}
public function getGuestAddress(): ?string
{
    return $this->guestAddress;
}
public function setGuestAddress(?string $a): static
{
    $this->guestAddress = $a;
    return $this;

}

}
