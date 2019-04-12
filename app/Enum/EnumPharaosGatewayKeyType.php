<?php

namespace App\Enum;

abstract class EnumPharaosGatewayKeyType {
    const PAYMENT = 1;

    const TYPE = [
        self::PAYMENT  => 'Pagamentos',
    ];

}
