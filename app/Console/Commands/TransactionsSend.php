<?php

namespace App\Console\Commands;

use App\Enum\EnumOperationType;
use App\Enum\EnumTransactionsStatus;
use App\Http\Controllers\OffScreenController;
use App\Mail\UnderAnalysisMail;
use App\Models\Coin;
use App\Models\Transaction;
use App\Models\TransactionFee;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TransactionsSend extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia transações pendentes para o core correspondente';

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
        $coins = Coin::where([
            'is_crypto' => true,
            'is_wallet' => true,
            'is_active' => true,
        ])->get();

        $authorizedTransactions = [];

        foreach ($coins as $coin) {
            $authorizedTransactions[$coin->abbr] = Transaction::listAuthorized($coin->id);
        }

        try {

            foreach ($authorizedTransactions as $coin_abbr => $transactionsList) {

                if (!count($transactionsList)) {
                    continue;
                }
                
                $data = [];
                foreach ($transactionsList as $transaction) {
                    $data[] = [
                        'fromAddress' => $transaction->wallet->address,
                        'toAddress' => $transaction->toAddress,
                        'fee' => $transaction->fee,
                        'amount' => $transaction->amount,
                        'balance' => sprintf("%.8f", $transaction->wallet->balance)
                    ];
                }

                $tx = OffScreenController::post(EnumOperationType::FIRST_SIGN_TRANSACTION, $data, $coin_abbr);

                if (count($tx['errors'])) {
                    $this->proccessErrors($tx['errors']);
                }

                if (count($tx['send'])) {
                    $this->proccessSent($tx['send'], $tx['txid']);
                }

                TransactionFee::create([
                    'txid' => $tx['txid'],
                    'is_paid' => false,
                    'amount' => $tx['feeDiff'],
                ]);

            }

        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    private function proccessErrors($errors)
    {
        foreach ($errors as $error) {
            $transactionR = Transaction::whereIn('status', [EnumTransactionsStatus::ERROR, EnumTransactionsStatus::AUTHORIZED])
                ->where([
                    'toAddress' => $error['toAddress'],
                    'fee' => $error['fee'],
                    'amount' => $error['amount'],
                ])->whereHas('wallet', function ($wallet) use ($error) {
                    return $wallet->where('address', $error['fromAddress']);
                })->first();

            if (!$transactionR) {
                continue;
            }

            $_errors = '';
            foreach ($error['errors'] as $_error) {
                $_errors .= "(" . $_error['id'] . ") " . $_error['message'] . " / ";

                if ($_error['id'] == "err-547") {
                    $this->blockUser($transactionR);
                }
            }

            $transactionR->update([
                'error' => $_errors,
                'status' => EnumTransactionsStatus::ERROR
            ]);
        }
    }

    private function proccessSent($transactions_sent, $txid)
    {
        foreach ($transactions_sent as $sent) {
            $transactionR = Transaction::whereIn('status', [EnumTransactionsStatus::ERROR, EnumTransactionsStatus::AUTHORIZED])
                ->where([
                    'toAddress' => $sent['toAddress'],
                    'fee' => $sent['fee'],
                    'amount' => $sent['amount'],
                ])->whereHas('wallet', function ($wallet) use ($sent) {
                    return $wallet->where('address', $sent['fromAddress']);
                })->first();

            if (!$transactionR) {
                continue;
            }

            $transactionR->update([
                'tx' => $txid,
                'error' => '',
                'status' => EnumTransactionsStatus::SUCCESS
            ]);
        }
    }

    private function blockUser($transaction)
    {
        if (env('CHECK_WALLETS_BALANCES')) {

            $user = User::where([
                'id' => $transaction->user_id,
                'is_under_analysis' => false
            ])->first();

            if ($user) {
                $user->is_under_analysis = true;
                $user->save();

                $user->tokens()->each(function ($token) {
                    $token->delete();
                });

                Mail::to($user->email)->send(new UnderAnalysisMail($user));
            }
        }
    }
}
