<?php

namespace App\Providers;

use App\Helpers\Validations;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class CpfValidatorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        Validator::extend('cpf', function ($attribute, $value, $parameters, $validator) {
            $value = $cpf = preg_replace("/[^0-9]/", "", $value);
            return Validations::validCpf($value);
        });
    }
}
