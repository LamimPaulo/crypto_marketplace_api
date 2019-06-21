<?php

namespace App\Http\Controllers\Admin;

use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\CoinQuote;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserWallet;
use App\NaviPayment;
use App\Services\BalanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class NaviPaymentController extends Controller
{
    public function index()
    {
        try {
            $payments = NaviPayment::orderBy('start_date', 'DESC');

            return response($payments->paginate(10), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function filter(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        try {

            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $navi = env('NAVI_QUOTE');

            $transactions = Transaction::where('created_at', '>=', $start_date . ' 00:00:00')
                ->where('created_at', '<=', $end_date . ' 23:59:59')
                ->whereNull('sender_user_id');

            $dolarQuote = CoinQuote::where([
                    'coin_id' => Coin::getByAbbr("USD")->id,
                    'quote_coin_id' => Coin::getByAbbr("BRL")->id]
            )->first()->average_quote;

            $btcQuote = CoinQuote::where([
                'coin_id' => Coin::getByAbbr("BTC")->id,
                'quote_coin_id' => Coin::getByAbbr("USD")->id
            ])->first()->average_quote;

            $trans = [];

            $categories = EnumTransactionCategory::TYPES;
            $total_usd = 0;
            foreach ($categories as $key => $cat) {
                $count = Transaction::where('created_at', '>=', $start_date . ' 00:00:00')
                    ->where('created_at', '<=', $end_date . ' 23:59:59')
                    ->whereNull('sender_user_id')
                    ->where('category', $key)->count();

                $value_usd = $count * $navi;

                $trans[] = [
                    'count' => $count,
                    'category' => $cat,
                    'value_usd' => $value_usd,
                    'value_btc' => sprintf("%.8f", $value_usd / $btcQuote),
                ];
                $total_usd +=$value_usd;
            }

            $total_btc =sprintf("%.8f", $total_usd / $btcQuote);
            return response([
                'navi_quote' => $navi,
                'dolar' => $dolarQuote,
                'btc' => $btcQuote,
                'start_date' => Carbon::parse($request->start_date)->format('d/m/Y'),
                'end_date' => Carbon::parse($request->end_date)->format('d/m/Y'),
                'total_transactions' => $transactions->count(),
                'transactions' => $trans,
                'amount_usd' => $total_usd,
                'amount_btc' => $total_btc
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function payment(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $start_date = $request->start_date;
            $end_date = $request->end_date;


            $payments_inicial = NaviPayment::where('start_date', '<=', $start_date)
                ->where('end_date', '>=', $start_date)
                ->count();
            if ($payments_inicial > 0) {
                throw new \Exception("Já foram solicitados pagamentos com a data incial selecionada");
            }

            $payments_final = NaviPayment::where('start_date', '<=', $end_date)
                ->where('end_date', '>=', $end_date)
                ->count();

            if ($payments_final > 0) {
                throw new \Exception("Já foram solicitados pagamentos com a data final selecionada");
            }


            $navi = env('NAVI_QUOTE');

            $transactions = Transaction::where('created_at', '>=', $start_date . ' 00:00:00')
                ->where('created_at', '<=', $end_date . ' 23:59:59')
                ->whereNull('sender_user_id');

            $dolarQuote = CoinQuote::where([
                    'coin_id' => Coin::getByAbbr("USD")->id,
                    'quote_coin_id' => Coin::getByAbbr("BRL")->id]
            )->first()->average_quote;

            $btcQuote = CoinQuote::where([
                'coin_id' => Coin::getByAbbr("BTC")->id,
                'quote_coin_id' => Coin::getByAbbr("USD")->id
            ])->first()->average_quote;

            $trans = [];

            $categories = EnumTransactionCategory::TYPES;
            foreach ($categories as $key => $cat) {
                $count = Transaction::where('created_at', '>=', $start_date . ' 00:00:00')
                    ->where('created_at', '<=', $end_date . ' 23:59:59')
                    ->whereNull('sender_user_id')
                    ->where('category', $key)->count();

                $trans[$key] = [
                    'type' => $key,
                    'count' => $count,
                    'description' => $cat,
                ];
            }

            $total = $transactions->count();
            $amountUsd = $total * $navi;
            $amountBTC = sprintf("%.8f", $amountUsd / $btcQuote);

            if ($amountBTC == 0) {
                throw new \Exception('Nenhuma solicitação foi registrada, pois o valor não atingiu o mínimo');
            }

            NaviPayment::create([
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'description' => json_encode($trans),
                'navi_quote' => $navi,
                'btc_quote' => $btcQuote,
                'usd_quote' => $dolarQuote,
                'total' => $total,
                'amount_btc' => $amountBTC,
                'amount_usd' => $amountUsd,
                'status' => 1
            ]);

            $wallet = UserWallet::where('address', env("NAVI_USER_BTC_ADDRESS"))->first();

            $transaction = Transaction::create([
                'user_id' => $wallet->user_id,
                'coin_id' => $wallet->coin_id,
                'wallet_id' => $wallet->id,
                'toAddress' => $wallet->address,
                'amount' => $amountBTC,
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::TRANSACTION,
                'fee' => 0,
                'tax' => 0,
                'tx' => Uuid::uuid4()->toString(),
                'info' => 'Recebimento sobre operações do Período '. Carbon::parse($request->start_date)->format('d/m/Y'). ' - '. Carbon::parse($request->end_date)->format('d/m/Y'),
                'error' => '',
            ]);

            TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
            ]);

            BalanceService::increments($transaction);
            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'success',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
