<?php

namespace App\Enum;

abstract class EnumInvestmentOperationType {
    const IN = 1;
    const PROFIT = 2;
    const DRAFT = 3;
    const PROFIT_DRAFT = 4;
    const PROFIT_IN = 5;
    const DRAFT_TOTAL = 6;
    const PROFIT_REFERRAL = 7;

    const STATUS = [
        self::IN => 'Investimento',
        self::PROFIT => 'Lucro',
        self::PROFIT_REFERRAL => 'Lucro Afiliado',
        self::DRAFT => 'Saque',
        self::PROFIT_DRAFT => 'Saque de Lucro',
        self::PROFIT_IN => 'Investimento de Lucro',
        self::DRAFT_TOTAL => 'Saque Total',
    ];
}
