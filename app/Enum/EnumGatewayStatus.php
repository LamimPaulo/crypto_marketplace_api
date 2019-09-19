<?php

namespace App\Enum;

abstract class EnumGatewayStatus {
    const PAID = 1;
    const SEEN = 2;
    const OVERPAIDEXPIRED = 3;
    const OVERPAID = 4;
    const UNDERPAIDEXPIRED = 5;
    const EXPIRED = 6;
    const DONE = 7;
    const INIT = 8;
    const NEWW = 9;
    const ACTIVE = 10;
    const RECENT = 11;
    const UNDERPAID = 12;


    const SITUATION = [
        self::PAID => "PAGO",
        self::SEEN => "VISTO",
        self::OVERPAIDEXPIRED => "PAGO ACIMA EXPIRADO",
        self::OVERPAID => "PAGO ACIMA",
        self::UNDERPAIDEXPIRED => "PAGO ABAIXO EXPIRADO",
        self::EXPIRED => "EXPIRADO",
        self::DONE => "FEITO",
        self::INIT => "INIT",
        self::NEWW => "PENDENTE",
        self::ACTIVE => "ACTIVE",
        self::RECENT => "RECENT",
        self::UNDERPAID => "PAGO ABAIXO",
    ];
}
