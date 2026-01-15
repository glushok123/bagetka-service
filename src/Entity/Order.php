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
    private ?bool $isSendSms = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pdf = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(nullable: false, enumType: OfficeType::class)]
    public OfficeType $officeType;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isImportant = false;

    #[ORM\Column(nullable: true)]
    private ?bool $isDeleted = false;

    #[ORM\Column(nullable: true)]
    private ?bool $isCreateManager = false;

    #[ORM\Column(nullable: true)]
    private ?bool $isFinished = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $jpeg = null;

    #[ORM\Column(name: 'duration_hours', type: Types::INTEGER, options: ['default' => 2])]
    private int $durationHours = 2;

    #[ORM\Column(name: 'receipt_pdf', length: 255, nullable: true)]
    private ?string $receiptPdf = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'receipt_employee_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Employee $receiptEmployee = null;

    #[ORM\Column(name: 'receipt_amount', length: 255, nullable: true)]
    private ?string $receiptAmount = null;

    #[ORM\Column(name: 'receipt_created_at', nullable: true)]
    private ?\DateTimeImmutable $receiptCreatedAt = null;

    public function getJpeg(): ?string
    {
        return $this->jpeg;
    }

    public function setJpeg(?string $jpeg): static
    {
        $this->jpeg = $jpeg;
        return $this;
    }

    public function getDurationHours(): int
    {
        return $this->durationHours;
    }

    public function setDurationHours(int $durationHours): static
    {
        $this->durationHours = $durationHours;
        return $this;
    }

    public function getReceiptPdf(): ?string
    {
        return $this->receiptPdf;
    }

    public function setReceiptPdf(?string $receiptPdf): static
    {
        $this->receiptPdf = $receiptPdf;
        return $this;
    }

    public function getReceiptEmployee(): ?Employee
    {
        return $this->receiptEmployee;
    }

    public function setReceiptEmployee(?Employee $receiptEmployee): static
    {
        $this->receiptEmployee = $receiptEmployee;
        return $this;
    }

    public function getReceiptAmount(): ?string
    {
        return $this->receiptAmount;
    }

    public function setReceiptAmount(?string $receiptAmount): static
    {
        $this->receiptAmount = $receiptAmount;
        return $this;
    }

    public function getReceiptCreatedAt(): ?\DateTimeImmutable
    {
        return $this->receiptCreatedAt;
    }

    public function setReceiptCreatedAt(?\DateTimeImmutable $receiptCreatedAt): static
    {
        $this->receiptCreatedAt = $receiptCreatedAt;
        return $this;
    }

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

    public function setIsSendSms(?bool $isSendSms): static
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

    public function setIsImportant(?bool $isImportant): static
    {
        $this->isImportant = $isImportant;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(?bool $isDeleted): static
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function isCreateManager(): ?bool
    {
        return $this->isCreateManager;
    }

    public function setIsCreateManager(?bool $isCreateManager): static
    {
        $this->isCreateManager = $isCreateManager;

        return $this;
    }

    public function isFinished(): ?bool
    {
        return $this->isFinished;
    }

    public function setIsFinished(?bool $isFinished): static
    {
        $this->isFinished = $isFinished;

        return $this;
    }
}
