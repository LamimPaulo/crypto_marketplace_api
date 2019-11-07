<?php

namespace App\Enum;

abstract class EnumGatewayType {
    const PAYMENT = 1;
    const WITHDRAW = 2;
    const MINING_PAYMENT = 3;

    const TYPE = [
      self::PAYMENT => 'PAYMENT',
      self::WITHDRAW => 'WITHDRAW',
      self::MINING_PAYMENT => 'MINING PAYMENT',
    ];
}
