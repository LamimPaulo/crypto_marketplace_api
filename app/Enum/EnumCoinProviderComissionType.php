<?php

namespace App\Enum;

abstract class EnumCoinProviderComissionType {
    const PERCENT = 1;
    const DECIMAL = 2;

    const TYPE = [
        self::PERCENT => 'Porcentagem',
        self::DECIMAL => 'Decimal',
    ];

}
