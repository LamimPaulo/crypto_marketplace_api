<?php

namespace App\Enum;

abstract class EnumUserLevelType {
    const NACIONAL = 1;
    const INTERNACIONAL = 2;

    const TYPE = [
        self::NACIONAL => 'Nacional',
        self::INTERNACIONAL => 'Internacional',
    ];

}
