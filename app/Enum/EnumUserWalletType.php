<?php

namespace App\Enum;

abstract class EnumUserWalletType
{
    const WALLET = 1;
    const PRODUCT = 2;
    const ACCOUNT = 3;
    const MASTERNODE = 4;

    const TYPES = [
        self::WALLET => 'Carteira',
        self::PRODUCT => 'Produto',
        self::ACCOUNT => 'Conta',
        self::MASTERNODE => 'Masternode',
    ];
}
