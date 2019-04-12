<?php

namespace App\Enum;

abstract class EnumFundType {
    const LIMITED = 1;
    const UNLIMITED = 2;

    const TYPE = [
        self::LIMITED => 'Limitado',
        self::UNLIMITED => 'Ilimitado',
    ];

}
