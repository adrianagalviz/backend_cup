<?php

namespace App\Helpers;

use Carbon\CarbonImmutable;

class DateHelper
{
    public static function now(): CarbonImmutable
    {
        return CarbonImmutable::now();
    }

    public static function nowForDatabase(): string
    {
        return self::now()->toDateTimeString();
    }
}
