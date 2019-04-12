<?php

namespace App\Enum;

abstract class EnumPaypalStatus {
    const created = 1;
    const approved = 3;
    const failed = 4;

    const STATUS = [
        'created'   => 1,
        'approved'  => 3,
        'failed'    => 4,
    ];
}
