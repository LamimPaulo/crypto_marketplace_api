<?php

namespace App\Enum;

abstract class EnumMasternodeStatus
{
    const PENDING = 1;
    const PROCESSING = 2;
    const SUCCESS = 3;
    const CANCELED = 4;
    const ERROR = 5;

    const STATUS = [
        self::PENDING => 'Pendente',
        self::PROCESSING => 'Em Ativação',
        self::SUCCESS => 'Ativado',
        self::CANCELED => 'Cancelado',
        self::ERROR => 'Erro',
    ];

    const COLOR = [
        self::PENDING => 'warning',
        self::PROCESSING => 'primary',
        self::SUCCESS => 'success',
        self::CANCELED => 'secondary',
        self::ERROR => 'danger',
    ];
}
