<?php

namespace App\Enum;

abstract class EnumNotificationChannel {
    const GENERAL = 1;
    const USER = 2;

    const CHANNEL = [
        self::GENERAL   => 'General',
        self::USER      => 'Usuário',
    ];

}
