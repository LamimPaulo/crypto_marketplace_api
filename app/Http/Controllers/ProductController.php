<?php

namespace App\Http\Controllers;

use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Http\Requests\BuyLevelRequest;
use App\Models\Coin;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User\UserLevel;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use App\User;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function buyLevel(BuyLevelRequest $request)
    {
        try {
            $level = UserLevel::with('product')->find($request->level_id);
            $coin = Coin::getByAbbr($request->abbr);
            $wallet = UserWallet::where(['coin_id' => $coin->id, 'user_id' => auth()->user()->id])->first();

            $bonus = Product::find(auth()->user()->user_level_id)->first()->bonus_percent;

            $amount = $level->product->value - ($level->product->value * $bonus / 100);

            if ($coin->abbr == 'LQX') {
                $amount = $level->product->value_lqx - ($level->product->value_lqx * $bonus / 100);
            }

            if (!(abs($amount) <= abs($wallet->balance))) {
                throw new \Exception(trans('messages.transaction.value_exceeds_balance') . " ($amount) <= {$wallet->balance}");
            }

            $user = User::where('id', auth()->user()->id)->first();

            if ($level->id <= $user->user_level_id) {
                throw new \Exception("Seu nível atual é mais avançado que o requisitado. A compra não pode ser efetuada.");
            }

            if (!$user->api_key) {
                $user->api_key = str_replace('-', '', Uuid::uuid4()->toString());
            }

            DB::beginTransaction();

            $transaction = Transaction::create([
                'user_id' => $wallet->user_id,
                'coin_id' => $wallet->coin_id,
                'wallet_id' => $wallet->id,
                'toAddress' => $wallet->address,
                'amount' => $amount,
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::OUT,
                'category' => EnumTransactionCategory::BUY_LEVEL,
                'fee' => 0,
                'tax' => 0,
                'tx' => Uuid::uuid4()->toString(),
                'info' => 'Compra de Keycode: ' . $level->name,
                'error' => '',
            ]);

            BalanceService::decrements($transaction);

            $user->user_level_id = $request->level_id;
            $user->save();

            DB::commit();

            return response([
                'message' => "Compra Efetuada com sucesso"
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
