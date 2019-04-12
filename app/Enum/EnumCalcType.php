<?php

namespace App\Enum;

abstract class EnumCalcType {
    const PERCENT = 1;
    const DECIMAL = 2;
    const FEE = 3;

    const TYPE = [
        self::PERCENT => 'Porcentagem',
        self::DECIMAL => 'Decimal',
        self::FEE => 'Fee',
    ];

}
