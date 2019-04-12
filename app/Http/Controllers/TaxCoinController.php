<?php

namespace App\Http\Controllers;

use App\Models\TaxCoin;
use App\Models\TaxCoinTransaction;
use Symfony\Component\HttpFoundation\Response;

class TaxCoinController extends Controller
{
    public function taxCoinTransactionCreate($taxCoins, $transaction)
    {
        foreach ($taxCoins as $taxCoin) {
            $tax = TaxCoinTransaction::create([
                'tax_coin_id' => $taxCoin->id,
                'crypto' => $taxCoin->value,
                'operation_type' => $taxCoin->operation,
                'operation_id' => $transaction->id
            ]);
        }
    }

    public function ted()
    {
        return response([
            'ted' => TaxCoin::where(["coin_id" => 2, "user_level_id" => auth()->user()->user_level_id])
                            ->first()->value],
            Response::HTTP_OK);
    }
}
