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
    case novokuznetsk = 'novokuznetsk';

   #[EnumCase(extras: ['nameRu' => 'Арбатская'])]
    case arbatskaya = 'arbatskaya';

    #[EnumCase(extras: ['nameRu' => 'Баррикадная'])]
    case barricade = 'barricade';


}
