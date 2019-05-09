<?php

namespace App\Enum;

abstract class EnumTransactionCategory
{
    const TRANSACTION = 1;
    const ORDER = 2;
    const DEPOSIT = 3;
    const WITHDRAWAL = 4;
    const GATEWAY = 5;
    const CONVERSION = 6;
    const LQX_SUBMIT = 7;
    const TRANSFER = 8;
    const ARBITRAGE = 9;
    const FUND = 10;
    const CREDMINER = 11;
    const BUY_LEVEL = 12;
    const NANOTECH_CREDMINER = 13;
    const MASTERNODE_CREDMINER = 14;
    const FUND_CREDMINER = 15;
    const NANOTECH = 16;
    const MASTERNODE = 17;

    const TYPES = [
        self::TRANSACTION => 'Transação',
        self::ORDER => 'Ordem',
        self::DEPOSIT => 'Depósito',
        self::WITHDRAWAL => 'Saque',
        self::GATEWAY => 'Gateway',
        self::CONVERSION => 'Conversão',
        self::LQX_SUBMIT => 'Envio de LQX',
        self::TRANSFER => 'Transferência',
        self::ARBITRAGE => 'Nanotech',
        self::NANOTECH_CREDMINER => 'Nanotech Credminer',
        self::FUND => 'Fundos de Investimento',
        self::CREDMINER => 'Saque Credminer',
        self::BUY_LEVEL => 'Compra de Keycode',
        self::FUND_CREDMINER => 'Fundos Credminer',
        self::MASTERNODE_CREDMINER => 'Masternode Credminer',
        self::NANOTECH=> 'Nanotech',
        self::MASTERNODE => 'Masternode',
    ];
}
