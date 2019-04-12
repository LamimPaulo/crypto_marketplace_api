<?php

namespace App\Enum;

abstract class EnumOrderTimeInForce {
    const GTC = 1;
    const IOC = 2;
    const FOK = 3;

    const TYPE = [
        self::GTC => 'Goo till Cancelled',
        self::IOC => 'Immediate or cancel',
        self::FOK => 'Fill or Kill',
    ];

}
