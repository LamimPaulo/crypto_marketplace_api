<?php

namespace App\Enum;

abstract class EnumGatewayCategory {
    const PAYMENT = 1;
    const MINING = 2;

    const CATEGORY = [
      self::PAYMENT => 'Pagamento',
      self::MINING => 'Compra de Mineração',
    ];
}
