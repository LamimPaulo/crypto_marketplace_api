<?php

namespace App\Enum;

abstract class EnumTokenAction
{
    const DRAFT = 1;
    const PAYMENT = 2;
    const BUY = 3;
    const SELL = 4;
    const SEND = 5;
    const PASS_CHANGE = 6;
    const PIN_CHANGE = 7;
    const PHONE_VERIFY = 8;
    const DATA_VERIFY = 9;
    const CONVERT_COIN = 10;
    const BUY_LEVEL = 11;
    const GATEWAY_KEY = 12;
    const FIAT_SEND = 13;
    const INVESTMENT_IN = 14;
    const INVESTMENT_OUT = 15;
    const INDEX_FUNDS_IN = 16;
    const INDEX_FUNDS_OUT = 17;
    const CANCEL_DRAFT = 18;
    const BRL_SUBMISSION = 19;

    const ACTION = [
        self::DRAFT => 'Saque',
        self::PAYMENT => 'Pagamento',
        self::BUY => 'Compra de Ativos',
        self::SELL => 'Venda de Ativos',
        self::SEND => 'Envio de crypto',
        self::PASS_CHANGE => 'Atualização de Senha',
        self::PIN_CHANGE => 'Atualização do PIN',
        self::PHONE_VERIFY => 'Verificação de Telefone',
        self::DATA_VERIFY => 'Atualização de Dados',
        self::CONVERT_COIN => 'Conversão de Moeda',
        self::GATEWAY_KEY => 'Gateway de Pagamentos',
        self::FIAT_SEND => 'Transferência de Valores',
        self::INVESTMENT_IN => 'Investimento',
        self::INVESTMENT_OUT => 'Saque de Investimento',
        self::INDEX_FUNDS_IN => 'Aquisição de Investimento',
        self::INDEX_FUNDS_OUT => 'Saque de Investimentos',
        self::CANCEL_DRAFT => 'Cancelamento de Saque',
        self::BUY_LEVEL => 'Compra de Keycode',
        self::BRL_SUBMISSION => 'Envio de R$ Credminer',
    ];
}
