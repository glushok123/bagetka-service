<?php

namespace App\Dto\StatusDay;

use App\Dto\BasicDto;
use DateTimeImmutable;

class StatusDayDto extends BasicDto
{
    public function __construct(
        public readonly ?string            $officeType = null,
        public readonly ?string            $typeDay = null,
        public readonly ?DateTimeImmutable $day = null,


    )
    {
    }
}