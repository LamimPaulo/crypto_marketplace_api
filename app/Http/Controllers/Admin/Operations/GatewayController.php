<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Enum\EnumGatewayCategory;
use App\Enum\EnumGatewayStatus;
use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GatewayController extends Controller
{
    public function index(Request $request)
    {
        try {
            $transactions = Gateway::with([
                'fiat_coin',
                'coin'
            ])->orderBy('created_at', 'DESC');

            if (!empty($request->term)) {
                $transactions->where('tx', 'LIKE', "%{$request->term}%")
                    ->orWhere('address', 'LIKE', "%{$request->term}%")
                    ->orWhere('txid', 'LIKE', "%{$request->term}%");
            }

            if (!empty($request->status)) {
                $transactions->where('status', $request->status);
            }

            if (!empty($request->category)) {
                $transactions->where('category', $request->category);
            } else {
                $transactions->where('category', EnumGatewayCategory::CREDMINER);
            }

            return response($transactions->paginate(10), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'transactions' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function status()
    {
        return EnumGatewayStatus::SITUATION;
    }

    public function transaction($address)
    {
        try {
            Gateway::where('address', $address)->firstOrFail();
            $transaction = Transaction::with('user')->where('toAddress', $address)->first();

            return response([
                'transaction' => $transaction
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'transaction' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
