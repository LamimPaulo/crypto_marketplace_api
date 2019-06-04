<?php

namespace App\Enum;

abstract class EnumGatewayPaymentCoin {
    const CRYPTO = 1;
    const FIAT = 2;

    const TYPE = [
        self::FIAT => 'FIAT',
        self::CRYPTO => 'CRYPTO',
    ];

}
