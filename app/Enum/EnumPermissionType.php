<?php

namespace App\Enum;

abstract class EnumPermissionType
{
    const DENIED = 0;
    const ACCESS = 1;
    const TOTAL = 2;

    const TYPE = [
        self::DENIED => 'Negada',
        self::ACCESS => 'Acesso',
        self::TOTAL => 'Total',
    ];

    const COLOR = [
        self::DENIED => 'danger',
        self::ACCESS => 'primary',
        self::TOTAL => 'success',
    ];
}
