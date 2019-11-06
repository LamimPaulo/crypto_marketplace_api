<?php

namespace App\Enum;

abstract class EnumMasternodeOperation {
    const ALLOC_NEW_ADDRESS = 1;
    const UPDATE_TXIDS = 2;
    const SUSPEND_NODE = 3;
    const REWARDS_SUM = 4;

    const OPERATION = [
        self::ALLOC_NEW_ADDRESS => "ALLOC NEW ADDRESS",
        self::UPDATE_TXIDS => "UPDATE TXIDS",
        self::SUSPEND_NODE => "SUSPEND NODE",
        self::REWARDS_SUM => "REWARDS SUM"
    ];
}
