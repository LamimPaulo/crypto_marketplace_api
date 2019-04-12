<?php

namespace App\Enum;

abstract class EnumGatewayPaymentCoin {
    const FIAT = 1;
    const CRYPTO = 2;

    const TYPE = [
        self::FIAT => 'FIAT',
        self::CRYPTO => 'CRYPTO',
    ];

}
