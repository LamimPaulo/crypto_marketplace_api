<?php

namespace App\Http\Controllers;

use App\Enum\EnumCalcType;
use App\Enum\EnumOperations;
use App\Enum\EnumTaxType;
use App\Models\Coin;
use App\Models\System\WithdrawalDeadline;
use App\Models\TaxCoin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WithdrawalDeadlineController extends Controller
{
    public function index()
    {
        try {

            $taxes = WithdrawalDeadline::where('status', true)->get();
            return response($taxes, Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'taxes' => []
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function calc(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'tax_id' => 'required|exists:withdrawal_deadlines,id',
        ]);

        try {

            $ted = TaxCoin::where([
                'coin_id' => Coin::getByAbbr('BRL')->id,
                'user_level_id' => auth()->user()->user_level_id,
                'coin_tax_type' => EnumTaxType::TED,
                'operation' => EnumOperations::FIAT_WITHDRAW
            ])->first();

            $tedTax = $ted->value ?? 0;
            if ($ted->calc_type == EnumCalcType::PERCENT) {
                $tedTax = $request->amount * ($ted->value / 100);
            }


            $deadline = WithdrawalDeadline::findOrFail($request->tax_id);
            $tax = $request->amount * $deadline->tax / 100;

            return [
                'tax' => number_format($tax, 2, ',', '.'),
                'ted' => number_format($tedTax, 2, ',', '.'),
                'total' => number_format($request->amount + $tax + $tedTax, 2, ',', '.'),
                'deadline' => Carbon::now()->addDays($deadline->deadline)->format('d/m/Y')
            ];

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

    }
}
