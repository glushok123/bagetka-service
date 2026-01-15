<?php

namespace App\Entity;

use App\Enum\OfficeType;
use App\Repository\EmployeeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $fullName;

    #[ORM\Column(nullable: false, enumType: OfficeType::class)]
    private OfficeType $officeType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getOfficeType(): OfficeType
    {
        return $this->officeType;
    }

    public function setOfficeType(OfficeType $officeType): self
    {
        $this->officeType = $officeType;

        return $this;
    }
}
