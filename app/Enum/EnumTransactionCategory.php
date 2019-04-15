<?php

namespace App\Enum;

abstract class EnumTransactionCategory
{
    const TRANSACTION = 1;
    const ORDER = 2;
    const DEPOSIT = 3;
    const DRAFT = 4;
    const GATEWAY = 5;
    const CONVERSION = 6;
    const MINING = 7;
    const TRANSFER = 8;
    const ARBITRAGE = 9;
    const INDEX_FUND = 10;
    const CREDMINER = 11;
    const BUY_LEVEL = 12;

    const TYPES = [
        self::TRANSACTION => 'Transação',
        self::ORDER => 'Ordem',
        self::DEPOSIT => 'Depósito',
        self::DRAFT => 'Saque',
        self::GATEWAY => 'Gateway',
        self::CONVERSION => 'Conversão',
        self::MINING => 'Mineração',
        self::TRANSFER => 'Transferência',
        self::ARBITRAGE => 'Arbitragem',
        self::INDEX_FUND => 'Index Fund',
        self::BUY_LEVEL => 'Compra de Nível',
    ];
}
