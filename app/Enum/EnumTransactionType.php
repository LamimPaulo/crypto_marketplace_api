<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Enum;

/**
 * Description of EnumTransactionType
 *
 * @author vagner
 */
abstract class EnumTransactionType {
    const IN = 1;
    const OUT = 2;

    const BUY = 'BUY';
    const SELL = 'SELL';

    const TYPES = [
        self::IN => 'IN',
        self::OUT => 'OUT'
    ];

    const SIDE = [
        self::BUY => 1,
        self::SELL => 2
    ];
}
