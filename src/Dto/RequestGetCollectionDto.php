<?php

namespace App\Dto;


class RequestGetCollectionDto extends BasicDto
{
    public function __construct(
        public readonly ?int    $page = null,
        public readonly ?string $officeType = null,
        public readonly ?int    $weekNumber = null,
    )
    {
    }
}