<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Transaction;
use App\Models\TransactionHist;
use App\Models\User\UserWallet;
use App\User;
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
                ->orderBy('name')->paginate(10);

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

    public function transactionUpdate(Request $request)
    {
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

            DB::statement("UPDATE transactions SET amount = {$request->amount}, fee = {$request->fee}, tax = {$request->tax}, type = {$request->type} WHERE id = {$transaction->id}");

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

    public function balanceUpdate(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'balance' => 'required|numeric',
            'abbr' => 'required|exists:coins,abbr',
        ]);

        try {
            DB::beginTransaction();

            $user = User::where('email', $request->email)->firstOrFail();

            $wallet = UserWallet::where([
                'user_id' => $user->id,
                'coin_id' => Coin::getByAbbr($request->abbr)->id,
            ])->firstOrFail();

            $wallet->balance = $request->balance;
            $wallet->save();

            //Offscreen
            //if ($wallet->coin->is_crypto AND $wallet->coin->abbr != "LQX" AND env("APP_ENV") != "local") {
            if ($wallet->coin->is_crypto AND $wallet->coin->abbr != "LQX") {
                $api = new \GuzzleHttp\Client(['http_errors' => false]);

                $url = str_replace("operation", "syncwallet", config("services.offscreen.{$request->abbr}"));

                $response = $api->post($url, [
                    \GuzzleHttp\RequestOptions::JSON => [
                        'amount' => $request->balance,
                        'address' => $wallet->address,
                        'key' => $request->password,
                    ]
                ]);

                $statuscode = $response->getStatusCode();

                if (401 === $statuscode OR 422 === $statuscode) {
                    throw new \Exception('Senha inválida.');
                }

                if (200 !== $statuscode && 201 !== $statuscode) {
                    throw new \Exception('Erro desconhecido [' . $statuscode . ']');
                }
            }

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
