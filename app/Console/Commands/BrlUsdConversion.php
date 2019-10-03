<?php

namespace App\Console\Commands;

use App\Enum\EnumOperationType;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Http\Controllers\OffScreenController;
use App\Http\Controllers\OrderController;
use App\Http\Requests\ConvertRequest;
use App\Models\Coin;
use App\Models\LqxWithdrawal;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use App\Services\ConversorService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class BrlUsdConversion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    //protected $signature = 'brlusd:conversion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fiat Wallet Balance conversion';

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
        try {

            $wallets = UserWallet::with('user')
                ->whereHas('coin', function ($coin) {
                    return $coin->where('is_crypto', false);
                })
                ->where('balance', '>', 0)
                ->get();

            foreach ($wallets as $wallet) {
                $request = new ConvertRequest();
                $request->user_id = $wallet->user->id;
                $request->country_id = $wallet->user->country_id;
                $request->amount = $wallet->balance;
                $request->base = $wallet->coin->abbr;
                $request->quote = "LQX";

                $this->convertAmount($request);

            }

        } catch
        (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage() . ' => ' . $e->getLine());
        }
    }

    public function convertAmount(ConvertRequest $request)
    {

        $amount = (float)$request->amount;
        $user_fiat_abbr = $request->country_id === 31 ? 'BRL' : 'USD';
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

        try {
            DB::beginTransaction();
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

            DB::commit();
            return response([
                'message' => trans('messages.transaction.conversion_success')
            ], Response::HTTP_CREATED);

        } catch (\Exception $ex) {
            DB::rollBack();
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
