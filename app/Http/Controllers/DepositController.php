<?php

namespace App\Http\Controllers;

use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Helpers\ActivityLogger;
use App\Http\Requests\DepositPaypalRequest;
use App\Http\Requests\DepositRequest;
use App\Models\Coin;
use App\Models\SysConfig;
use App\Models\System\SystemAccount;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class DepositController extends Controller
{
    public function store(DepositRequest $request)
    {
        try {

            $deposit = Transaction::where(['type' => EnumTransactionType::IN, 'category' => EnumTransactionCategory::DEPOSIT, 'status' => EnumTransactionsStatus::PENDING, 'user_id' => auth()->user()->id])->first();
            if ($deposit) {
                throw new \Exception(trans('messages.deposit.already_pending'));
            }

            DB::beginTransaction();
            $depositMin = SysConfig::first()->deposit_min_valor;
            $coin = auth()->user()->country_id == 31 ? Coin::getByAbbr('BRL')->id : Coin::getByAbbr('USD')->id;
            $amount = abs($request->amount);
            $userWallet = UserWallet::where(['user_id' => auth()->user()->id, 'coin_id' => $coin])->first();

            $depositMin_ = auth()->user()->country_id == 31 ? number_format($depositMin, 2, ',', '.') : sprintf("%.2", $depositMin);

            if ($amount < $depositMin) {
                throw new \Exception(trans('messages.deposit.value_not_reached_min'));
            }

            $file = $this->uploadFile($request);

            $transaction = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $userWallet->coin_id,
                'wallet_id' => $userWallet->id,
                'amount' => $amount,
                'status' => EnumTransactionsStatus::PENDING,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::DEPOSIT,
                'confirmation' => 0,
                'system_account_id' => $request->system_account_id,
                'file_path' => $file,
                'tx' => Uuid::uuid4()->toString()
            ]);

            TransactionStatus::create([
                'transaction_id' => $transaction->id,
                'status' => $transaction->status
            ]);

            ActivityLogger::log(trans('messages.deposit.sent'), $transaction->id, Transaction::class, $transaction);

            DB::commit();
            return response([
                'message' => trans('messages.deposit.sent'),
                'deposit' => $transaction
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    private function uploadFile($request)
    {
        $uuid4 = Uuid::uuid4();
        $extension = $request->file('file')->getClientOriginalExtension();
        $nameFile = $uuid4->toString() . ".{$extension}";
        $request->file('file')->storeAs("navi/ico/" . auth()->user()->id . "/deposit/", $nameFile);
        $file_path = "navi/ico/" . auth()->user()->id . "/deposit/$nameFile";
        return $file_path;
    }

    public function storePaypal(DepositPaypalRequest $request)
    {
        try {

            DB::beginTransaction();
            $coin = auth()->user()->country_id == 31 ? Coin::getByAbbr('BRL')->id : Coin::getByAbbr('USD')->id;
            $amount = abs($request->amount);
            $userWallet = UserWallet::where(['user_id' => auth()->user()->id, 'coin_id' => $coin])->first();

            $system_account = SystemAccount::findOrFail($request->system_account_id);

            $transaction = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $userWallet->coin_id,
                'wallet_id' => $userWallet->id,
                'amount' => $amount,
                'status' => EnumTransactionsStatus::PENDING,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::DEPOSIT,
                'confirmation' => 0,
                'system_account_id' => $request->system_account_id,
                'tx' => $request->payment_id
            ]);

            TransactionStatus::create([
                'transaction_id' => $transaction->id,
                'status' => $transaction->status
            ]);

            ActivityLogger::log(trans('messages.deposit.sent'), $transaction->id, Transaction::class, $transaction);

            DB::commit();
            return response([
                'message' => trans('messages.deposit.sent'),
                'deposit' => $transaction
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
