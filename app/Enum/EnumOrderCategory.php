<?php

namespace App\Enum;

abstract class EnumOrderCategory
{
    const ASSETS = 1;
    const FUNDS = 2;

    const CATEGORY = [
        self::ASSETS => 'Ativos',
        self::FUNDS => 'Fundos de Investimento',
    ];

}
