<?php

namespace App\Http\Controllers\Mining;

use App\Enum\EnumMiningProfitType;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Http\Controllers\Controller;
use App\Models\Mining\MiningPlan;
use App\Models\Mining\MiningQuota;
use App\Models\Mining\MiningQuotaProfit;
use App\Models\Coin;
use App\Models\Mining\MiningBlock;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use App\User;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class MiningQuotaController extends Controller
{
    public function index()
    {
        try {
            DB::beginTransaction();

            $balanceService = new BalanceService();
            $coin = Coin::getByAbbr('BTC')->id;
            $payout = MiningPlan::first()->profit_payout;

            $users = User::with(['profits' => function ($profits) {
                    return $profits->where('is_paid', 0)->orderBy('date_found');
                }])
                ->whereHas('profits', function ($profits) {
                    return $profits->where('is_paid', 0)->orderBy('date_found');
                })
                ->get();


            //pega todos os usuarios que tem direito a lucro
            foreach ($users as $user) {
                $sumProfits = 0;
                $profitsPassed = [];

                //listar cada lucro existente
                foreach ($user->profits as $profit) {
                    $sumProfits += $profit->reward;

                    array_push($profitsPassed, $profit);

                    if ($sumProfits >= $payout) {
                        $tx = Uuid::uuid4();
                        $userWallet = UserWallet::where(['user_id' => $user->id, 'coin_id' => $coin, 'type' => EnumUserWalletType::WALLET])->first()->id;
                        $transaction = Transaction::create([
                            'user_id' => $user->id,
                            'coin_id' => $coin,
                            'wallet_id' => $userWallet,
                            'toAddress' => '',
                            'amount' => $sumProfits,
                            'status' => EnumTransactionsStatus::SUCCESS,
                            'type' => EnumTransactionType::IN,
                            'category' => EnumTransactionCategory::MINING,
                            'fee' => 0,
                            'tax' => 0,
                            'tx' => $tx->toString(),
                            'info' => '',
                            'error' => '',
                        ]);

                        TransactionStatus::create([
                            'status' => $transaction->status,
                            'transaction_id' => $transaction->id,
                        ]);

                        $balanceService::increments($transaction);

                        foreach ($profitsPassed as $pf){
                            $pf->is_paid = 1;
                            $pf->save();
                        }

                        $profitsPassed = [];
                        $sumProfits = 0;
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }

    }

    public function profit()
    {
        try {
            DB::beginTransaction();

            $mining = MiningPlan::first();

            $miningBlocks = MiningBlock::where('is_paid', 0)->where('is_mature', 1)->get();
            foreach ($miningBlocks as $block) {
                echo "<pre>{$block->block} | {$block->reward} | {$block->date_found} </pre>";

                $miningQuota = MiningQuota::where('ths_quota', '>', 0)->where('created_at', '<=', $block->date_found)->get();
                foreach ($miningQuota as $quota) {
                    echo "<pre>{$quota->user_id} | {$quota->ths_quota} Th/s | {$quota->created_at}</pre>";
                    if ($mining->profit_type === EnumMiningProfitType::PERCENT) {

                        $full_reward = $block->reward * ($mining->profit / 100);
                        $reward = sprintf("%.8f", ($full_reward / $mining->ths_total) * $quota->ths_quota);
                        echo "<pre>{$quota->ths_quota} = {$reward}</pre><br><br>";
                        MiningQuotaProfit::create([
                            'user_id' => $quota->user_id,
                            'block' => $block->block,
                            'ths_quota' => $quota->ths_quota,
                            'reward' => $reward,
                            'is_paid' => 0
                        ]);
                    }
                }

                $block->is_paid = true;
                $block->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }

    }
}
