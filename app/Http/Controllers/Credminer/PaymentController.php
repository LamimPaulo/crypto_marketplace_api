<?php

namespace App\Http\Controllers\Credminer;

use App\Enum\EnumCalcType;
use App\Enum\EnumGatewayStatus;
use App\Enum\EnumGatewayType;
use App\Enum\EnumOperations;
use App\Enum\EnumTaxType;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckCpfRequest;
use App\Http\Requests\CheckKeyRequest;
use App\Http\Requests\WithdrawalCredminerRequest;
use App\Models\Coin;
use App\Models\CoinQuote;
use App\Models\Gateway;
use App\Models\SysConfig;
use App\Models\Transaction;
use App\Models\User\UserWallet;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function checkKey(CheckKeyRequest $request)
    {
        try {
            $user = User::with('level')
                ->where('api_key', '=', $request->get('api_key'))->first();

            if (is_null($user)) {
                throw new \Exception('Api Key is invalid!');
            }

            return response([
                'message' => 'Api Key is valid',
                'user' => $user->name,
                'user_type' => $user->country_id == 31 ? 1 : 2,
                'user_type_name' => $user->country_id == 31 ? 'brasileiro' : 'internacional',
                'document' => $user->document,
                'level' => [
                    'name' => $user->level->name,
                    'nanotech_lqx_fee' => $user->level->nanotech_lqx_fee,
                    'nanotech_btc_fee' => $user->level->nanotech_btc_fee,
                    'masternode_fee' => $user->level->masternode_fee,
                    'tax_crypto' => $user->level->tax_crypto->makeHidden(['id', 'coin_id', 'user_level_id', 'created_at', 'updated_at', 'description']),
                    'tax_brl' => $user->level->tax_brl->makeHidden(['id', 'coin_id', 'user_level_id', 'created_at', 'updated_at', 'description']),
                ],
                'dictionary' => [
                    'tax_types' => EnumTaxType::OPERATIONS,
                    'calc_types' => EnumCalcType::TYPE,
                    'operation_types' => EnumOperations::OPERATIONS,
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            return response([
                'error' => $ex->getMessage()
            ], Response::HTTP_OK);
        }
    }

    public function checkCpf(CheckCpfRequest $request)
    {
        try {
            $user = User::whereHas('level', function ($level) {
                return $level->whereNotIn('id', [1, 7]);
            })
                ->where('document', $request->cpf)
                ->whereNotNull('api_key')
                ->first();

            if (is_null($user)) {
                throw new \Exception("Operação não permitida, Cliente sem keycode " . env("APP_NAME") . ".");
            }

            return response([
                'message' => 'Cliente válido',
                'user' => $user->name,
                'user_type' => $user->country_id == 31 ? 'brasileiro' : 'internacional',
                'document' => $user->document,
                'level' => $user->level->name
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Recupera o pagamento de fatura
     *
     * @param int $payment
     * @return type
     */
    public function showPayment($payment)
    {
        try {
            $gatewayModel = Gateway::where('tx', '=', $payment)
                ->where('type', '=', EnumGatewayType::PAYMENT)
                ->first();

            if (!is_null($gatewayModel)) {
                $total = ($gatewayModel->amount + $gatewayModel->taxas);
                $histStatus = $gatewayModel->histStatus;
                $coin = $gatewayModel->coin;
                $total = sprintf('%.' . $coin->decimal . 'f', floatval($total));

                foreach ($histStatus as $key => $value) {
                    $histStatus[$key]->name = EnumGatewayStatus::SITUATION[$histStatus[$key]->status];
                }

                $sysConfig = SysConfig::first();

                $created = Carbon::parse($gatewayModel->created_at)->addMinutes($sysConfig->time_gateway);
                $current = Carbon::now();
                $diff = $current->diffInMinutes($created);

                return response([
                    'total' => $total,
                    'status' => $gatewayModel->statusName,
                    'address' => $gatewayModel->address,
                    'created' => $created->format('M d, Y H:i:s'),
                    'expired' => $diff,
                    'hist_status' => $gatewayModel->histStatus
                ], Response::HTTP_OK);
            }
            return response([
                'error' => 'Recurso não encontrado!',
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $ex) {
            return response([
                'error' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function withdrawal(WithdrawalCredminerRequest $request)
    {
        try {

            $user = User::where('api_key', '=', $request->get('api_key'))->first();

            if (!$user) {
                throw new \Exception("Api Key Inválida.");
            }

            $quote_coin = $user->country_id == 31 ? Coin::getByAbbr("BRL")->id : Coin::getByAbbr("USD")->id;

            $coin = Coin::getByAbbr($request->coin);

            $wallet = UserWallet::where([
                'user_id' => $user->id,
                'coin_id' => $coin->id,
                'type' => EnumUserWalletType::WALLET
            ])->firstOrFail();

            $request->request->add([
                'fromAddress' => $wallet->address
            ]);

            $cotacao = CoinQuote::where([
                'coin_id' => $coin->id,
                'quote_coin_id' => $quote_coin,
            ])->first();

            DB::beginTransaction();

            $transaction = Transaction::create([
                'user_id' => $wallet->user_id,
                'coin_id' => $wallet->coin_id,
                'wallet_id' => $wallet->id,
                'toAddress' => $wallet->address,
                'amount' => $request->amount,
                'status' => EnumTransactionsStatus::PENDING,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::CREDMINER,
                'fee' => 0,
                'tax' => 0,
                'tx' => Uuid::uuid4()->toString(),
                'info' => 'Saque Credminer',
                'error' => '',
                'price' => $cotacao->buy_quote,
                'market' => $cotacao->average_quote,
            ]);

            DB::commit();

            return response([
                'amount' => $transaction->amount,
                'coin' => $request->coin,
                'tx' => $transaction->tx,
                'created' => $transaction->created_at,
                'status' => EnumTransactionsStatus::STATUS[$transaction->status]
            ], Response::HTTP_CREATED);
        } catch (\Exception $ex) {

            DB::rollBack();
            return response([
                'error' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

}
