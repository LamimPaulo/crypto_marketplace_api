<?php

namespace App\Enum;

abstract class EnumNanotechOperationStatus {
    const PENDING = 1;
    const SUCCESS = 3;
    const REVERSED = 8;

    const STATUS = [
        self::PENDING => 'Pendente',
        self::SUCCESS => 'Sucesso',
        self::REVERSED => 'Estornado',
    ];
}
