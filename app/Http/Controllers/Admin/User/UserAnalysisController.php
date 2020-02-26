<?php

namespace App\Http\Controllers\Admin\User;

use App\Enum\EnumUserWalletType;
use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Transaction;
use App\Models\TransactionHist;
use App\Models\User\UserWallet;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UserAnalysisController extends Controller
{
    public function list()
    {
        try {
            $users = User::with(['level'])
                ->where('is_under_analysis', true)
                ->orderBy('user_level_id', 'DESC')->orderBy('name')->paginate(10);

            return response($users
                , Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function search(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3'
        ]);

        try {
            $users = User::with(['level'])
                ->where('is_under_analysis', true)
                ->where('name', 'like', "%{$request->name}%")
                ->orWhere('email', 'like', "%{$request->name}%")
                ->orWhere('document', 'like', "%{$request->name}%")
                ->orderBy('name', 'ASC')->get();

            return response(['data' => $users]
                , Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function release($email)
    {
        try {
            $user = User::where('email', $email)->firstOrFail();
            $user->is_under_analysis = false;
            $user->save();

            return response([
                'message' => "Usuário reativado com sucesso!"
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function block($email)
    {
        try {
            $user = User::where('email', $email)->firstOrFail();
            $user->is_under_analysis = true;
            $user->save();

            return response([
                'message' => "Usuário bloqueado com sucesso!"
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function transactionUpdate(Request $request)
    {
        $request->validate([
            'coin_id' => 'required|exists:coins,id',
            'amount' => 'required|numeric',
            'fee' => 'required|numeric',
            'tax' => 'required|numeric',
            'type' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::findOrFail($request->id);

            TransactionHist::create([
                "transaction_id" => $transaction->id,
                "user_id" => $transaction->user_id,
                "coin_id" => $transaction->coin_id,
                "wallet_id" => $transaction->wallet_id,
                "toAddress" => $transaction->toAddress,
                "amount" => $transaction->amount,
                "fee" => $transaction->fee,
                "status" => $transaction->status,
                "type" => $transaction->type,
                "category" => $transaction->category,
                "tx" => $transaction->tx,
                "confirmation" => $transaction->confirmation,
                "info" => $transaction->info,
                "error" => $transaction->error,
                "sender_user_id" => $transaction->sender_user_id,
                "is_gateway_payment" => $transaction->is_gateway_payment,
                "system_account_id" => $transaction->system_account_id,
                "user_account_id" => $transaction->user_account_id,
                "file_path" => $transaction->file_path,
                "tax" => $transaction->tax,
                "price" => $transaction->price,
                "market" => $transaction->market,
                "payment_at" => $transaction->payment_at,
                "transaction_created_at" => $transaction->created_at,
                "transaction_updated_at" => $transaction->updated_at,
                "creator_user_id" => auth()->user()->id
            ]);

            DB::statement("UPDATE transactions SET amount = {$request->amount}, coin_id = {$request->coin_id}, fee = {$request->fee}, tax = {$request->tax}, type = {$request->type} WHERE id = {$transaction->id}");

            DB::commit();
            return response([
                'message' => "Transação alterada com sucesso!"
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function transactionDelete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:transactions,id',
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::findOrFail($request->id);

            TransactionHist::create([
                "transaction_id" => $transaction->id,
                "user_id" => $transaction->user_id,
                "coin_id" => $transaction->coin_id,
                "wallet_id" => $transaction->wallet_id,
                "toAddress" => $transaction->toAddress,
                "amount" => $transaction->amount,
                "fee" => $transaction->fee,
                "status" => $transaction->status,
                "type" => $transaction->type,
                "category" => $transaction->category,
                "tx" => $transaction->tx,
                "confirmation" => $transaction->confirmation,
                "info" => $transaction->info,
                "error" => $transaction->error,
                "sender_user_id" => $transaction->sender_user_id,
                "is_gateway_payment" => $transaction->is_gateway_payment,
                "system_account_id" => $transaction->system_account_id,
                "user_account_id" => $transaction->user_account_id,
                "file_path" => $transaction->file_path,
                "tax" => $transaction->tax,
                "price" => $transaction->price,
                "market" => $transaction->market,
                "payment_at" => $transaction->payment_at,
                "transaction_created_at" => $transaction->created_at,
                "transaction_updated_at" => $transaction->updated_at,
                "creator_user_id" => auth()->user()->id
            ]);

            DB::statement("SET FOREIGN_KEY_CHECKS=0");
            DB::statement("DELETE FROM transactions WHERE id = {$transaction->id}");
            DB::statement("SET FOREIGN_KEY_CHECKS=1");

            DB::commit();
            return response([
                'message' => "Transação excluida com sucesso!"
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function transactionDuplicate(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:transactions,id',
            'new_tx' => 'required',
            'new_updated' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::findOrFail($request->id);
            $newTransaction = $transaction->replicate();
            $newTransaction->tx = $request->new_tx;
            $newTransaction->info = $request->info;
            $newTransaction->type = $request->type;
            $newTransaction->toAddress = $request->toAddress;
            $newTransaction->category = $request->category;
            $newTransaction->created_at = $transaction->created_at;
            $newTransaction->updated_at = $request->new_updated;
            $newTransaction->save();

            DB::commit();
            return response([
                'message' => "Transação duplicada com sucesso!"
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function balanceUpdate(Request $request)
    {
        $request->validate([
            'balance' => 'required|numeric',
            'abbr' => 'required|exists:coins,abbr',
        ]);

        try {
            DB::beginTransaction();

            $user = User::where('email', $request->email)->firstOrFail();

            $wallet = UserWallet::where([
                'user_id' => $user->id,
                'coin_id' => Coin::getByAbbr($request->abbr)->id,
                'type' => EnumUserWalletType::WALLET,
            ])->firstOrFail();

            $wallet->balance = $request->balance;
            $wallet->save();

            DB::commit();
            return response([
                'message' => "Balance alterado com sucesso!"
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

}
