<?php

namespace App\Enum;

abstract class EnumMiningProfitType {
    const DECIMAL = 1;
    const CRYPTO = 2;
    const PERCENT = 3;

    const TYPE = [
        self::DECIMAL => 'Fixo',
        self::CRYPTO => 'Crypto',
        self::PERCENT => 'Porcentagem',
    ];

}
