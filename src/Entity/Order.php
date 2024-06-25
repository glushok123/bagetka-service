<?php

namespace App\Entity;

use App\Enum\OfficeType;
use App\Repository\OrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $number = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isSendSms = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pdf = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(nullable: false, enumType: OfficeType::class)]
    public OfficeType $officeType;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isImportant = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isDeleted = null;

    public function getOfficeType(): ?OfficeType
    {
        return $this->officeType;
    }

    public function getOfficeTypeNameRu(): ?string
    {
        return $this->officeType->getExtra('nameRu');
    }

    public function setOfficeType(?OfficeType $type): static
    {
        $this->officeType = $type;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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

    public function isSendSms(): ?bool
    {
        return $this->isSendSms;
    }

    public function setSendSms(?bool $isSendSms): static
    {
        $this->isSendSms = $isSendSms;

        return $this;
    }

    public function getPdf(): ?string
    {
        return $this->pdf;
    }

    public function setPdf(?string $pdf): static
    {
        $this->pdf = $pdf;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function isImportant(): ?bool
    {
        return $this->isImportant;
    }

    public function setImportant(?bool $isImportant): static
    {
        $this->isImportant = $isImportant;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setDeleted(?bool $isDeleted): static
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }
}
