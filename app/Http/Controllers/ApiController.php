<?php

namespace App\Http\Controllers;

use App\Enum\EnumOperationType;
use App\Enum\EnumTransactionsStatus;
use App\Mail\UnderAnalysisMail;
use App\Models\Coin;
use App\Models\CoinQuote;
use App\Models\Transaction;
use App\Models\TransactionFee;
use App\Services\ConversorService;
use App\User;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function index(Request $request)
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
                $data = [];

                if(!count($transactionsList)){
                    continue;
                }

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

    public function lqx()
    {
        $lqx = Coin::getByAbbr("LQX")->id;
        $usd = Coin::getByAbbr("USD")->id;
        $btc = Coin::getByAbbr("BTC")->id;

        $lqx_usd = CoinQuote::where([
            'coin_id' => $lqx,
            'quote_coin_id' => $usd
        ])->first()->average_quote;

        $btc_usd = CoinQuote::where([
            'coin_id' => $btc,
            'quote_coin_id' => $usd
        ])->first()->average_quote;

        $btc_lqx = ConversorService::FIAT2CRYPTO_MAX($lqx_usd, "BTC", "USD")['amount'];


        $api = new \GuzzleHttp\Client(['http_errors' => false]);

        $response = $api->get('https://www.dashcentral.org/api/v1/public');
        $statuscode = $response->getStatusCode();

        if (200 !== $statuscode) {
            return
                [
                    "general" => [
                        "consensus_blockheight" => 0,
                        "consensus_version" => 0,
                        "consensus_protocolversion" => 0,
                        "consensus_masternodes" => 0,
                        "all_user" => 0,
                        "registered_masternodes" => 0,
                        "registered_masternodes_verified" => 0
                    ],
                    "exchange_rates" => [
                        "dash_usd" => sprintf("%.8f", $lqx_usd),
                        "btc_usd" => sprintf("%.8f", $btc_usd),
                        "btc_dash" => sprintf("%.8f", $btc_lqx),
                    ],
                ];
        }

        $result = json_decode($response->getBody()->getContents(), 1);

        return
            [
                "general" => [
                    "consensus_blockheight" => $result['general']['consensus_blockheight'],
                    "consensus_version" => $result['general']['consensus_version'],
                    "consensus_protocolversion" => $result['general']['consensus_protocolversion'],
                    "consensus_masternodes" => $result['general']['consensus_masternodes'],
                    "all_user" => $result['general']['all_user'],
                    "registered_masternodes" => $result['general']['registered_masternodes'],
                    "registered_masternodes_verified" => $result['general']['registered_masternodes_verified'],
                ],
                "exchange_rates" => [
                    "dash_usd" => sprintf("%.8f", $lqx_usd),
                    "btc_usd" => sprintf("%.8f", $btc_usd),
                    "btc_dash" => sprintf("%.8f", $btc_lqx),
                ],
            ];
    }
}
