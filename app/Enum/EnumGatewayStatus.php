<?php

namespace App\Enum;

abstract class EnumGatewayStatus
{
    const NOTFOUND = 0;
    const PAID = 1;
    const SEEN = 2;
    const OVERPAIDEXPIRED = 3;
    const OVERPAID = 4;
    const UNDERPAIDEXPIRED = 5;
    const EXPIRED = 6;
    const DONE = 7;
    const DONEEXPIRED = 8;
    const NEWW = 9;
    const PAIDEXPIRED = 10;
    const RECENT = 11;
    const UNDERPAID = 12;
    const REVERSED = 13;

    const SITUATION = [
        self::REVERSED => "DEVOLVIDO",
        self::EXPIRED => "EXPIRADO",
        self::DONE => "FEITO",
        self::DONEEXPIRED => "FEITO EXPIRADO",
        self::NOTFOUND => "NAO ENCONTRADO",
        self::PAID => "PAGO",
        self::UNDERPAID => "PAGO ABAIXO",
        self::UNDERPAIDEXPIRED => "PAGO ABAIXO EXPIRADO",
        self::OVERPAID => "PAGO ACIMA",
        self::OVERPAIDEXPIRED => "PAGO ACIMA EXPIRADO",
        self::PAIDEXPIRED => "PAGO EXPIRADO",
        self::NEWW => "PENDENTE",
        self::RECENT => "RECENTE",
        self::SEEN => "VISTO",
    ];

    const CONFIRMATION = [
        self::OVERPAIDEXPIRED,
        self::OVERPAID,
        self::UNDERPAIDEXPIRED,
        self::DONE,
        self::DONEEXPIRED,
        self::UNDERPAID,
    ];

    const NOTIFY = [
        self::EXPIRED,
        self::NEWW,
    ];
}
