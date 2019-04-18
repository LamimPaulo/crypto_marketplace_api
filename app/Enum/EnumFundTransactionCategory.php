<?php

namespace App\Enum;

abstract class EnumFundTransactionCategory {
    const PURCHASE = 1;
    const PROFIT = 2;
    const WITHDRAWAL = 3;
    const PROFIT_WITHDRAWAL = 4;
    const EARLY_WITHDRAWAL = 5;

    const CATEGORY = [
        self::PURCHASE => 'Aquisição de Investimento',
        self::PROFIT => 'Lucro',
        self::WITHDRAWAL => 'Retirada',
        self::PROFIT_WITHDRAWAL => 'Retirada de Lucro',
        self::EARLY_WITHDRAWAL => 'Retirada Antecipada',
    ];
}
