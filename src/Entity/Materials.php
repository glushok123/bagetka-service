<?php

namespace App\Entity;

use App\Enum\OfficeType;
use App\Repository\MaterialsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaterialsRepository::class)]
class Materials
{
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

    #[ORM\Column(nullable: false, enumType: OfficeType::class)]
    public OfficeType $officeType;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;


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

    public function setImportant(?bool $isImportant): static
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

    public function setWork(?bool $isWork): static
    {
        $this->isWork = $isWork;

        return $this;
    }
}
