<?php

namespace App\Console\Commands;

use App\Enum\EnumMiningProfitType;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
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
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class MiningProfits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mining:profits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Mining Profits';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $this->generate();
        $this->payment();
    }

    public function generate()
    {
        try {
            DB::beginTransaction();

            $mining = MiningPlan::first();

            $miningBlocks = MiningBlock::where('is_paid', 0)->where('is_mature', 1)->get();
            foreach ($miningBlocks as $block) {

                $miningQuota = MiningQuota::where('ths_quota', '>', 0)->where('created_at', '<=', $block->date_found)->get();
                foreach ($miningQuota as $quota) {

                    if ($mining->profit_type === EnumMiningProfitType::PERCENT) {

                        $full_reward = $block->reward * ($mining->profit / 100);
                        $reward = sprintf("%.8f", ($full_reward / $mining->ths_total) * $quota->ths_quota);

                        MiningQuotaProfit::create([
                            'user_id' => $quota->user_id,
                            'block' => $block->block,
                            'ths_quota' => $quota->ths_quota,
                            'reward' => $reward,
                            'date_found' => $block->date_found,
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
            throw new \Exception($e->getMessage());
        }
    }

    public function payment()
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

            foreach ($users as $user) {
                $sumProfits = 0;
                $profitsPassed = [];

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

                        foreach ($profitsPassed as $pf) {
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
            throw new \Exception($e->getMessage());
        }
    }
}
