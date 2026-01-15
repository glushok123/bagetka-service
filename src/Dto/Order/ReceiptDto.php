<?php

namespace App\Dto\Order;

use App\Dto\BasicDto;

class ReceiptDto extends BasicDto
{
    public function __construct(
        public readonly ?int $orderId = null,
        public readonly ?int $employeeId = null,
        public readonly ?string $amount = null,
    )
    {
    }
}
