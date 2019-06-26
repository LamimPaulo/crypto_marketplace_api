<?php

namespace App\Enum;

abstract class EnumUserTicketsDepartments
{
    const GENERAL = 1;
    const SUBMIT_COINS = 2;
    const WITHDRAWALS_DEPOSITS = 3;
    const KEYCODE = 4;
    const DOCS = 5;

    const DEPARTMENT = [
        self::GENERAL => 'Dúvidas Gerais',
        self::SUBMIT_COINS => 'Envio de Moedas',
        self::WITHDRAWALS_DEPOSITS => 'Saques e Depósitos',
        self::KEYCODE => 'Keycode e 2FA',
        self::DOCS => 'Validação de Documentos',
    ];
}
