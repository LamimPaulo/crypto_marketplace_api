<?php

namespace App\Enum;

abstract class EnumUserLevelLimitType
{
    const INTERNAL = 1;
    const EXTERNAL = 2;

    const TYPES = [
        self::INTERNAL => 'Envio Interno',
        self::EXTERNAL => 'Envio Externo'
    ];
}
