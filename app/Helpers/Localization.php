<?php

namespace App\Helpers;


class Localization
{
    public static function setLocale($user)
    {
        $locale = config('app.locale');

        if (isset($user->country_id)) {
            if ($user->country_id != 31) {
                $locale = 'en';
            }
        }

        app()->setLocale($locale);
    }
}
