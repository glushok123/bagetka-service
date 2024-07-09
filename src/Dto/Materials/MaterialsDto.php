<?php

namespace App\Dto\Materials;

use App\Dto\BasicDto;
use DateTimeImmutable;

class MaterialsDto extends BasicDto
{
    public function __construct(
        public readonly ?int    $id = null,
        public readonly ?int    $materialId = null,
        public readonly ?string $text = null,
        public readonly ?string $comment = null,
        public readonly ?bool   $isImportant = false,
        public readonly ?string $officeType = null,
        public readonly ?bool   $isFinished = false,
        public readonly ?bool   $isWork = false,
        public readonly ?DateTimeImmutable $date = null,
    )
    {
    }
}