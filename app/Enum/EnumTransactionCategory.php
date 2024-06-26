<?php

namespace App\Enum;

abstract class EnumTransactionCategory
{
    const TRANSACTION = 1;          //IN - OUT
    const ORDER = 2;                //IN - OUT
    const DEPOSIT = 3;              //OUT
    const WITHDRAWAL = 4;           //OUT
    const POS = 5;                  //IN
    const CONVERSION = 6;           //IN - OUT
    const LQX_SUBMIT = 7;           //OUT
    const TRANSFER = 8;             //IN - OUT
    const ARBITRAGE = 9;            //IN - OUT
    const FUND = 10;                //IN - OUT
    const CREDMINER = 11;           //IN
    const BUY_LEVEL = 12;           //OUT
    const NANOTECH_CREDMINER = 13;  //IN - OUT
    const MASTERNODE_CREDMINER = 14;//IN - OUT
    const FUND_CREDMINER = 15;      //IN - OUT
    const NANOTECH = 16;            //IN - OUT
    const MASTERNODE = 17;          //OUT
    const BRL_SUBMIT = 18;          //OUT
    const USD_SUBMIT = 19;          //OUT
    const LQX_WITHDRAWAL = 20;      //IN - OUT
    const LQX_REVERSION = 21;       //IN - OUT
    const MASTERNODE_REWARD = 22;   //IN
    const MASTERNODE_UNDO = 23;     //IN

    const TYPES = [
        self::TRANSACTION => 'Transação',
        self::ORDER => 'Ordem',
        self::DEPOSIT => 'Depósito',
        self::WITHDRAWAL => 'Saque',
        self::POS => 'Pagamento POS',
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
        self::MASTERNODE => 'Mensalidade Masternode',
        self::BRL_SUBMIT => 'Envio de R$ Credminer',
        self::USD_SUBMIT => 'Submited USD Credminer',
        self::LQX_WITHDRAWAL => 'Resgate Automático de Saldo',
        self::LQX_REVERSION => 'Estorno Gateway',
        self::MASTERNODE_REWARD => 'Recompensa Masternode',
        self::MASTERNODE_UNDO => 'Desfazimento de Masternode',
    ];
}
