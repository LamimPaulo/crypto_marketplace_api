<?php

namespace App\Enum;

abstract class EnumFundBalanceType
{
    const BLOCKED = 0;
    const FREE = 1;

    const TYPE = [
        self::BLOCKED => 'Bloqueado',
        self::FREE => 'Livre',
    ];

}
