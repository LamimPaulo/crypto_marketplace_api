<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Enum;

/**
 * Description of EnumTaxType
 *
 * @author vagner
 */
abstract class EnumTaxType
{
    const TED = 1;
    const OPERACAO = 2;
    const SISTEMA = 3;
    const FEE = 4;


    const OPERATIONS = [
        self::TED => 'TED',
        self::OPERACAO => 'Operação',
        self::SISTEMA => 'Sistema',
        self::FEE => 'Fee',
    ];
}
