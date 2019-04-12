<?php

namespace App\Enum;

abstract class EnumExchangeOperation {
    const TRADE = 1;
    const DRAFT = 2;

    const TYPES = [
        self::TRADE => 'TRADE',
        self::DRAFT => 'SAQUE'
    ];
}
