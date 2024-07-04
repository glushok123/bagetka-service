<?php

namespace App\Enum;

use Elao\Enum\Attribute\EnumCase;
use Elao\Enum\Attribute\ReadableEnum;
use Elao\Enum\ExtrasTrait;
use Elao\Enum\ReadableEnumInterface;
use Elao\Enum\ReadableEnumTrait;

#[ReadableEnum(prefix: 'officeType.', useValueAsDefault: true)]
enum RoleUser: string implements ReadableEnumInterface
{
    use ExtrasTrait, ReadableEnumTrait;

    #[EnumCase(extras: ['nameRu' => 'Админ'])]
    case admin = 'Админ';

   #[EnumCase(extras: ['nameRu' => 'Арбатская'])]
    case manager = 'Менеджер';

    #[EnumCase(extras: ['nameRu' => 'Баррикадная'])]
    case master = 'Мастер';

}
