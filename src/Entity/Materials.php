<?php

namespace App\Entity;

use App\Enum\OfficeType;
use App\Repository\MaterialsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaterialsRepository::class)]
class Materials
{
    #[ORM\Column(nullable: false, enumType: OfficeType::class)]
    public OfficeType $officeType;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;
    #[ORM\Column(nullable: true)]
    private ?bool $isImportant = null;
    #[ORM\ManyToOne(inversedBy: 'materials')]
    private ?User $user = null;
    #[ORM\Column(nullable: true)]
    private ?bool $isWork = null;
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isFinished = null;


    public function getOfficeType(): ?OfficeType
    {
        return $this->officeType;
    }

    public function setOfficeType(?OfficeType $type): static
    {
        $this->officeType = $type;

        return $this;
    }

    public function getOfficeTypeNameRu(): ?string
    {
        return $this->officeType->getExtra('nameRu');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

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

    public function isImportant(): ?bool
    {
        return $this->isImportant;
    }

    public function setIsImportant(?bool $isImportant): static
    {
        $this->isImportant = $isImportant;

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

    public function isWork(): ?bool
    {
        return $this->isWork;
    }

    public function setIsWork(?bool $isWork): static
    {
        $this->isWork = $isWork;

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
