<?php

namespace App\Http\Controllers\Notify;

use App\Enum\EnumOperationType;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GatewayController;
use App\Http\Controllers\OffScreenController;
use App\Models\Coin;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use App\Services\ConversorService;
use App\Services\TaxCoinService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

//use App\Http\Controllers\WalletTempController;

class BTCController extends Controller
{

    protected $balanceService;
    protected $conversorService;
    protected $taxCoinService;

    public function __construct(
        BalanceService $balance,
        ConversorService $conversor,
        TaxCoinService $taxCoin)
    {
        $this->conversorService = $conversor;
        $this->taxCoinService = $taxCoin;
        $this->balanceService = $balance;
    }

    public static function create($data)
    {
        try {
            $data = [
                'toAddress' => $data['toAddress'],
                'blockHash' => "",
                'amount' => $data['amount'],
                'fee' => $data['fee'],
                'tx' => $data['txid'],
                'user_id' => $data['user_id'],
                'coin_id' => $data['coin_id'],
                'wallet_id' => $data['wallet_id'],
                'vout' => $data['vout'],
                'status' => EnumTransactionsStatus::PENDING,
                'type' => EnumTransactionType::IN,
                'category' => $data['category'] ?? EnumTransactionCategory::TRANSACTION,
                'confirmation' => $data['confirmations'] ?? 0,
                'tax' => 0,
                'created_at' => $data['timestamp'] ? Carbon::parse($data['timestamp']) : Carbon::now()
            ];

            $transaction = Transaction::create($data);
            TransactionStatus::create([
                'transaction_id' => $transaction->id,
                'status' => $transaction->status
            ]);

            return $transaction;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public static function notify($data)
    {
        try {
            $transactionController = Transaction::where([
                'tx' => $data['txid'],
                'toAddress' => $data['toAddress'],
                'vout' => $data['vout'],
            ])
                ->where('type', '<>', EnumTransactionType::OUT)
                ->first();

            if (!$transactionController) {
                //Transaction
                $wallet = UserWallet::where([
                    'address' => $data['toAddress'],
                    'type' => EnumUserWalletType::WALLET
                ])->first();

                if ($wallet) {
                    $data['user_id'] = $wallet->user_id;
                    $data['coin_id'] = $wallet->coin_id;
                    $data['wallet_id'] = $wallet->id;
                    $transactionsCreate = self::create($data);
                    return $transactionsCreate;
                }

                //Masternode
                $masternode = UserWallet::where([
                    'address' => $data['toAddress'],
                    'type' => EnumUserWalletType::MASTERNODE
                ])->first();

                if ($masternode) {
                    $data['user_id'] = $masternode->user_id;
                    $data['coin_id'] = $masternode->coin_id;
                    $data['wallet_id'] = $masternode->id;
                    $data['category'] = EnumTransactionCategory::MASTERNODE_REWARD;

                    $transactionsCreate = self::create($data);
                    return $transactionsCreate;
                }

                //Gateway
                $result = GatewayController::update($data);
                DB::commit();
                return response([
                    'message' => $result
                ], Response::HTTP_CREATED);

            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public static function confirmation($transaction)
    {
        try {
            DB::beginTransaction();
            $result = OffScreenController::post(EnumOperationType::CONFIRMATION, ['txid' => $transaction->tx], Coin::find($transaction->coin_id)->abbr);

            $data['confirmation'] = $result['confirmations'];

            if ($data['confirmation'] >= 6) {
                $data['status'] = EnumTransactionsStatus::SUCCESS;

                self::balanceService()->increments($transaction);

                TransactionStatus::create([
                    'transaction_id' => $transaction->id,
                    'status' => $transaction->status
                ]);
            }

            $transaction->update($data);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            return $ex->getMessage();
        }
    }

    public static function balanceService()
    {
        return new BalanceService();
    }
}
