<?php

namespace App\Enum;

abstract class EnumMasternodeStatus
{
    const PENDING = 1;
    const PROCESSING = 2;
    const SUCCESS = 3;
    const CANCELED = 4;
    const ERROR = 5;
    const SUSPENDED = 6;
    const PENDING_PAYMENT = 7;
    const REFUSED = 8;

    const STATUS = [
        self::PENDING => 'Pendente',
        self::PROCESSING => 'Em Ativação',
        self::SUCCESS => 'Ativo',
        self::CANCELED => 'Cancelado',
        self::ERROR => 'Erro',
        self::SUSPENDED => 'Suspenso',
        self::PENDING_PAYMENT => 'Pagamento Pendente',
        self::REFUSED => 'Recusado',
    ];

    const STATUS_ = [
        self::SUCCESS => 'Ativo',
        self::PENDING_PAYMENT => 'Pagamento Pendente',
        self::REFUSED => 'Recusado',
    ];

    const TYPE = [
        self::PENDING => 'pending',
        self::PROCESSING => 'processing',
        self::SUCCESS => 'success',
        self::CANCELED => 'canceled',
        self::ERROR => 'error',
        self::SUSPENDED => 'suspended',
        self::PENDING_PAYMENT => 'pending_payment',
        self::REFUSED => 'refused',
    ];

    const COLOR = [
        self::PENDING => 'warning',
        self::PROCESSING => 'primary',
        self::SUCCESS => 'success',
        self::CANCELED => 'secondary',
        self::ERROR => 'danger',
        self::SUSPENDED => 'danger',
        self::PENDING_PAYMENT => 'danger',
        self::REFUSED => 'danger',
    ];
}
