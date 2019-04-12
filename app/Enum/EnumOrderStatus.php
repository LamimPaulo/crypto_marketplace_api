<?php

namespace App\Enum;

abstract class EnumOrderStatus {
    const NEW = 1;
    const PARTIALLY_FILLED = 2;
    const FILLED = 3;
    const CANCELED = 4;
    const REJECTED = 5;
    const EXPIRED = 6;

    const TYPE = [
        self::NEW => 'Enviada',
        self::PARTIALLY_FILLED => 'Preenchida Parcialmente',
        self::FILLED => 'Preenchida',
        self::CANCELED => 'Cancelada',
        self::REJECTED => 'Rejeitada',
        self::EXPIRED => 'Expirada',
    ];

}
