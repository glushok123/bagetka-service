<?php

namespace App\Enum;

use Elao\Enum\Attribute\EnumCase;
use Elao\Enum\Attribute\ReadableEnum;
use Elao\Enum\ExtrasTrait;
use Elao\Enum\ReadableEnumInterface;
use Elao\Enum\ReadableEnumTrait;

#[ReadableEnum(prefix: 'officeType.', useValueAsDefault: true)]
enum OfficeType: string implements ReadableEnumInterface
{
    use ExtrasTrait, ReadableEnumTrait;

    #[EnumCase(extras: ['nameRu' => 'Новокузнецкая'])]
    case novokuznetsk = 'Новокузнецкая';

   #[EnumCase(extras: ['nameRu' => 'Арбатская'])]
    case arbatskaya = 'Арбатская';

    #[EnumCase(extras: ['nameRu' => 'Баррикадная'])]
    case barricade = 'Баррикадная';

    #[EnumCase(extras: ['nameRu' => 'Менеджер'])]
    case manager = 'Менеджер';

    #[EnumCase(extras: ['nameRu' => 'Все'])]
    case all = 'Все';


}
