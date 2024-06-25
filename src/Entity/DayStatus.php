<?php

namespace App\Entity;

use App\Enum\OfficeType;
use App\Repository\DayStatusRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DayStatusRepository::class)]
class DayStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isClose = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $day = null;

    #[ORM\Column(nullable: false, enumType: OfficeType::class)]
    public OfficeType $officeType;

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

    public function isClose(): ?bool
    {
        return $this->isClose;
    }

    public function setClose(?bool $isClose): static
    {
        $this->isClose = $isClose;

        return $this;
    }

    public function getDay(): ?\DateTimeInterface
    {
        return $this->day;
    }

    public function setDay(\DateTimeInterface $day): static
    {
        $this->day = $day;

        return $this;
    }
}
