<?php

namespace App\Enum;

abstract class EnumTransactionsStatus {
    const PENDING = 1;
    const PROCESSING = 2;
    const SUCCESS = 3;
    const CANCELED = 4;
    const INSUFICIENT = 5;
    const ABOVELIMIT = 6;
    const ERROR = 7;
    const REVERSED = 8;
    const AUTHORIZED = 9;
    const TO_RELEASE = 10;
    const BLOCKED = 11;


    const STATUS = [
        self::PENDING => 'Pendente',
        self::PROCESSING => 'Processando',
        self::SUCCESS => 'Sucesso',
        self::CANCELED => 'Cancelada',
        self::INSUFICIENT => 'Fundos Insuficientes',
        self::ABOVELIMIT => 'Acima do Limite',
        self::ERROR => 'Erro',
        self::REVERSED => 'Estornada',
        self::AUTHORIZED => 'Autorizada',
        self::BLOCKED => 'Bloqueado',
        self::TO_RELEASE => 'Lançamento Futuro',
    ];

    const STATUS_CLIENT = [
        self::PENDING => 'Pendente',
        self::PROCESSING => 'Processando',
        self::SUCCESS => 'Sucesso',
        self::CANCELED => 'Cancelada',
        self::INSUFICIENT => 'Fundos Insuficientes',
        self::ABOVELIMIT => 'Pendente',
        self::ERROR => 'Pendente',
        self::REVERSED => 'Estornada',
        self::AUTHORIZED => 'Autorizada',
        self::BLOCKED => 'Bloqueado',
        self::TO_RELEASE => 'Lançamento Futuro',
    ];

    const STATUS_DRAFT = [
        self::PENDING => 'EFETUADO',
        self::PROCESSING => 'PROCESSANDO',
        self::SUCCESS => 'PAGO',
        self::REVERSED => 'ESTORNADO',
    ];

    const STATUS_CLASS = [
        self::PENDING => 'warning',
        self::PROCESSING => 'primary',
        self::SUCCESS => 'success',
        self::CANCELED => 'danger',
        self::INSUFICIENT => 'danger',
        self::ABOVELIMIT => 'warning',
        self::ERROR => 'danger',
        self::REVERSED => 'primary',
        self::AUTHORIZED => 'success',
        self::BLOCKED => 'danger',
        self::TO_RELEASE => 'warning',
    ];
}
