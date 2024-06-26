<?php

namespace App\Console\Commands;

use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Http\Requests\ConvertRequest;
use App\Models\Coin;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class BalancesToLqx extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balancesto:lqx';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert Balances to LQX Wallet';

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
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();

        try {

            $wallets = UserWallet::with(['user', 'coin'])
                ->where([
                    'sync' => false,
                    'type' => EnumUserWalletType::WALLET
                ])
                ->whereIn('coin_id', Coin::whereIn('abbr', ['BTC', 'LTC', 'BCH', 'DASH'])->pluck('id'))
                ->orderBy('balance')
                ->get();

            foreach ($wallets as $wallet) {

                if ($wallet->balance >= 0.00001) {
                    DB::beginTransaction();

                    $output->writeln("<info>-----------------------------</info>");
                    $output->writeln("<info>{$wallet->user->email}</info>");
                    $output->writeln("<info>{$wallet->coin->abbr}: {$wallet->balance}</info>");

                    $lqx_wallet = UserWallet::with('coin')
                        ->whereHas('coin', function ($coin) {
                            return $coin->where('abbr', 'LIKE', 'LQX');
                        })
                        ->where([
                            'user_id' => $wallet->user_id,
                            'is_active' => 1,
                            'type' => EnumUserWalletType::WALLET
                        ])->first();

                    if (!$lqx_wallet) {
                        $output->writeln("<info>No Lqx Wallet</info>");
                        continue;
                    }

                    $request = new ConvertRequest();
                    $request->user_id = $wallet->user->id;
                    $request->amount = $wallet->balance;
                    $request->base = $wallet->coin->abbr;
                    $request->quote = "LQX";

                    $this->convertAmount($request);

                    $wallet->sync = true;
                    $wallet->save();

                    DB::commit();
                }
            }
        } catch
        (\Exception $e) {
            DB::rollBack();
            $output->writeln("<info>{$e->getMessage()}</info>");
            $output->writeln("<info>{$e->getLine()} - {$e->getFile()}</info>");
        }
    }

    public function convertAmount(ConvertRequest $request)
    {
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();
        //$output->writeln("<info>{$request}</info>");
        //throw new \Exception('Parou');

        try {
            $amount = (float)$request->amount;
            $user_fiat_abbr = 'BRL';
            $fiat_coin = Coin::with('quote')->where('abbr', $user_fiat_abbr)->first();

            $base_coin = Coin::with([
                'quote' => function ($coin) use ($fiat_coin) {
                    return $coin->with('quote_coin')->where('quote_coin_id', $fiat_coin->id);
                }
            ])->where('abbr', $request->base)->first();

            $quote_coin = Coin::with([
                'quote' => function ($coin) use ($fiat_coin) {
                    return $coin->with('quote_coin')->where('quote_coin_id', $fiat_coin->id);
                }
            ])->where('abbr', $request->quote)->first();

            $result_sell = $base_coin->quote[0]->sell_quote * $amount;
            $result_buy = $result_sell / $quote_coin->quote[0]->buy_quote;

            $result = [
                'amount_buy' => $result_buy,
                'sell_current' => $base_coin->quote[0]->average_quote,
                'sell_quote' => $base_coin->quote[0]->sell_quote,
                'buy_current' => $quote_coin->quote[0]->average_quote,
                'buy_quote' => $quote_coin->quote[0]->sell_quote,
            ];

            $uuid = Uuid::uuid4();

            $wallet_out = UserWallet::where(['coin_id' => $base_coin->id, 'user_id' => $request->user_id])->first()->id;
            $wallet_in = UserWallet::where(['coin_id' => $quote_coin->id, 'user_id' => $request->user_id])->first()->id;
            $transaction_out = Transaction::create([
                'user_id' => $request->user_id,
                'coin_id' => $base_coin->id,
                'wallet_id' => $wallet_out,
                'amount' => $amount,
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::OUT,
                'category' => EnumTransactionCategory::CONVERSION,
                'fee' => 0,
                'tax' => 0,
                'tx' => $uuid->toString(),
                'info' => '',
                'error' => '',
                'price' => $result['sell_current'],
                'market' => $result['sell_quote']
            ]);

            TransactionStatus::create([
                'status' => $transaction_out->status,
                'transaction_id' => $transaction_out->id,
            ]);

            $transaction_in = Transaction::create([
                'user_id' => $request->user_id,
                'coin_id' => $quote_coin->id,
                'wallet_id' => $wallet_in,
                'amount' => $result['amount_buy'],
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::CONVERSION,
                'fee' => 0,
                'tax' => 0,
                'tx' => $uuid->toString(),
                'info' => '',
                'error' => '',
                'price' => $result['buy_current'],
                'market' => $result['buy_quote']
            ]);

            TransactionStatus::create([
                'status' => $transaction_in->status,
                'transaction_id' => $transaction_in->id,
            ]);

            BalanceService::decrements($transaction_out);
            BalanceService::increments($transaction_in);

        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage() . ' - ' . $ex->getLine() . ' - ' . $ex->getFile());
        }
    }
}
