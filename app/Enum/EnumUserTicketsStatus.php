<?php

namespace App\Enum;

abstract class EnumUserTicketsStatus
{
    const WAIT_USER = 1;
    const PENDING = 2;
    const SUCCESS = 3;
    const CANCELED = 4;


    const STATUS = [
        self::PENDING => 'Aberto',
        self::SUCCESS => 'Concluído',
        self::CANCELED => 'Cancelado',
        self::WAIT_USER => 'Aguardando Cliente',
    ];

    const STATUS_CLASS = [
        self::PENDING => 'warning',
        self::SUCCESS => 'success',
        self::CANCELED => 'default',
        self::WAIT_USER => 'warning',
    ];
}
