<?php

namespace App\Enum;

abstract class EnumMasternodeOperation
{
    const ALLOC_NEW_ADDRESS = 1;
    const UPDATE_TXIDS = 2;
    const SUSPEND_NODE = 3;
    const REWARDS_SUM = 4;
}
