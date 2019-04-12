<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\PaymentProvider;

class BankController extends Controller
{
    public function index()
    {
        $db_banks = Bank::orderBy('main')->orderBy('name')->get();
        $banks = [];
        foreach ($db_banks as $i => $bank) {
            $banks[$i] = [
                'id' => $bank->id,
                'name' => $bank->code . ' - ' . $bank->name
            ];
        }
        return $banks;
    }

    public function providers()
    {
        $providers = PaymentProvider::where('id', '2')->orderBy('name')->get();
        return $providers;
    }
}
