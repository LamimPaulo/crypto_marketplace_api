<?php

namespace App\Enum;

abstract class EnumGatewayCategory {
    const POS = 1;
    const CREDMINER = 2;
    const PAY2P = 3;

    const CATEGORY = [
      self::POS => 'Pagamento POS',
      self::CREDMINER => 'Pagamento Credminer',
      self::PAY2P => 'Pagamento Pay2P',
    ];
}
