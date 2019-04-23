<?php

namespace App\Enum;

abstract class EnumWeekdays
{
    const DOM = 0;
    const SEG = 1;
    const TER = 2;
    const QUA = 3;
    const QUI = 4;
    const SEX = 5;
    const SAB = 6;

    const STR = [
        self::DOM => 'Domingo',
        self::SEG => 'Segunda',
        self::TER => 'Terça',
        self::QUA => 'Quarta',
        self::QUI => 'Quinta',
        self::SEX => 'Sexta',
        self::SAB => 'Sábado',
    ];

    const NUM = [
        'Domingo' => self::DOM,
        'Segunda' => self::SEG,
        'Terça' => self::TER,
        'Quarta' => self::QUA,
        'Quinta' => self::QUI,
        'Sexta' => self::SEX,
        'Sábado' => self::SAB,
    ];

}
