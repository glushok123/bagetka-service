<?php

namespace App\Dto\Order;

use App\Dto\BasicDto;
use DateTimeImmutable;

class OrderDto extends BasicDto
{
    public function __construct(
        public readonly ?int               $id = null,
        public readonly ?int               $orderId = null,
        public readonly ?int               $number = null,
        public readonly ?string            $phone = null,
        public readonly ?bool              $isSendSms = null,
        public readonly ?string            $pdf = null,
        public readonly ?string            $comment = null,
        public readonly ?bool              $isImportant = null,
        public readonly ?bool              $isDeleted = null,
        public readonly ?bool              $isFinished = null,
        public readonly ?string            $officeType = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $date = null,

    )
    {
    }
}