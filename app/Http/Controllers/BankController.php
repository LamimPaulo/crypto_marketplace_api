<?php

namespace App\Http\Controllers;

use App\Models\Bank;

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
}
