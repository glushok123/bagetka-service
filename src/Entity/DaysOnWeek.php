<?php

namespace App\Entity;

use App\Repository\DaysOnWeekRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DaysOnWeekRepository::class)]
class DaysOnWeek
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateDay = null;

    #[ORM\Column]
    private ?int $weekNumber = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isCloseDay = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isCloseArbat = false;

    #[ORM\Column(nullable: true)]
    private ?bool $isCloseNov = false;

    #[ORM\Column(nullable: true)]
    private ?bool $isCloseBarricad = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDay(): ?\DateTimeInterface
    {
        return $this->dateDay;
    }

    public function setDateDay(\DateTimeInterface $dateDay): static
    {
        $this->dateDay = $dateDay;

        return $this;
    }

    public function getWeekNumber(): ?int
    {
        return $this->weekNumber;
    }

    public function setWeekNumber(int $weekNumber): static
    {
        $this->weekNumber = $weekNumber;

        return $this;
    }

    public function isCloseDay(): ?bool
    {
        return $this->isCloseDay;
    }

    public function setCloseDay(?bool $isCloseDay): static
    {
        $this->isCloseDay = $isCloseDay;

        return $this;
    }

    public function isCloseArbat(): ?bool
    {
        return $this->isCloseArbat;
    }

    public function setIsCloseArbat(?bool $isCloseArbat): static
    {
        $this->isCloseArbat = $isCloseArbat;

        return $this;
    }

    public function isCloseNov(): ?bool
    {
        return $this->isCloseNov;
    }

    public function setIsCloseNov(?bool $isCloseNov): static
    {
        $this->isCloseNov = $isCloseNov;

        return $this;
    }

    public function isCloseBarricad(): ?bool
    {
        return $this->isCloseBarricad;
    }

    public function setIsCloseBarricad(?bool $isCloseBarricad): static
    {
        $this->isCloseBarricad = $isCloseBarricad;

        return $this;
    }
}
