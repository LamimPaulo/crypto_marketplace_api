<?php

namespace App\Enum;

abstract class EnumMiningPriceType {
    const DECIMAL = 1;
    const CRYPTO = 2;

    const TYPE = [
        self::DECIMAL => 'Decimal',
        self::CRYPTO => 'Crypto',
    ];

}
