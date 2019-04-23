<?php

namespace App\Enum;

abstract class EnumNanotechOperationType {
    const IN = 1;
    const PROFIT = 2;
    const WITHDRAWAL = 3;
    const PROFIT_WITHDRAWAL = 4;
    const PROFIT_IN = 5;
    const TOTAL_WITHDRAWAL = 6;
    const PROFIT_REFERRAL = 7;

    const STATUS = [
        self::IN => 'Investimento',
        self::PROFIT => 'Lucro',
        self::PROFIT_REFERRAL => 'Lucro Afiliado',
        self::WITHDRAWAL => 'Saque',
        self::PROFIT_WITHDRAWAL => 'Saque de Lucro',
        self::PROFIT_IN => 'Investimento de Lucro',
        self::TOTAL_WITHDRAWAL => 'Saque Total',
    ];
}
