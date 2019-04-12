<?php

namespace App\Enum;

class EnumStatusDocument
{
    const NOTFOUND = 0;
    const PENDING = 1;
    const VALID = 2;
    const INVALID = 3;

    const STATUS = [
        self::NOTFOUND => 'not_found',
        self::PENDING => 'pending',
        self::VALID => 'valid',
        self::INVALID => 'invalid',
    ];

    const MESSAGE = [
        'pt_BR' => [
            self::NOTFOUND => 'Arquivo ainda não enviado',
            self::PENDING => 'Documento em processo de verificação.',
            self::VALID => 'Documento validado.',
            self::INVALID => 'Documento reprovado.',
        ],
        'en' => [
            self::NOTFOUND => 'File not sent.',
            self::PENDING => 'Document in process of verification.',
            self::VALID => 'Valid document.',
            self::INVALID => 'Failed document.',
        ],
    ];
}
