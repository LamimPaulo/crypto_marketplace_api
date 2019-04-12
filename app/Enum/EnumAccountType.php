<?php

namespace App\Enum;

abstract class EnumAccountType {
    const BANK = 1;
    const ONLINE = 2;

    const TYPE = [
        self::BANK   => 'Conta Bancária',
        self::ONLINE => 'Pagamentos Online',
    ];

}
