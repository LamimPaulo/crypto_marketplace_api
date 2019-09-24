<?php

namespace App\Http\Controllers;

use App\Enum\EnumGatewayPaymentCoin;
use App\Enum\EnumGatewayStatus;
use App\Enum\EnumOperations;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserLevelLimitType;
use App\Enum\EnumUserWalletType;
use App\Helpers\ActivityLogger;
use App\Helpers\Validations;
use App\Http\Requests\SendCryptoRequest;
use App\Http\Requests\TransferRequest;
use App\Models\Coin;
use App\Models\CoinQuote;
use App\Models\Gateway;
use App\Models\SysConfig;
use App\Models\TaxCoin;
use App\Models\TaxCoinTransaction;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserFavAccount;
use App\Models\User\UserLevelLimit;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use App\Services\ConversorService;
use App\Services\GatewayService;
use App\Services\TaxCoinService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class TransactionsController extends Controller
{
    protected $from = null;
    protected $txHash = null;
    protected $balanceService;
    protected $conversorService;
    protected $taxCoinService;
    protected $gatewayService;

    public function __construct(
        BalanceService $balance,
        ConversorService $conversor,
        TaxCoinService $taxCoin,
        GatewayService $gateway)
    {
        $this->conversorService = $conversor;
        $this->taxCoinService = $taxCoin;
        $this->balanceService = $balance;
        $this->gatewayService = $gateway;
    }

    public function store(SendCryptoRequest $request)
    {
        try {

            DB::beginTransaction();
            $from = UserWallet::where([
                'user_id' => auth()->user()->id,
                'address' => $request->address,
                'type' => EnumUserWalletType::WALLET])
                ->whereHas('coin', function ($coin) {
                    return $coin->where('is_crypto', true);
                })->first();

            if ($from->coin->abbr!='LQX') {
                throw new \Exception("O Envio de moedas está temporariamente bloqueado, somente poderão ser realizados os envios de LQX!");
            }

            if (!$from) {
                throw new \Exception(trans('messages.wallet.invalid'));
            }

            if (!$from->is_active) {
                throw new \Exception(trans('messages.wallet.inactive'));
            }

            if (!$from->coin->is_active) {
                throw new \Exception(trans('messages.coin.inactive'));
            }

            $pending = Transaction::where([
                'category' => EnumTransactionCategory::TRANSACTION,
                'coin_id' => $from->coin_id,
                'type' => EnumTransactionType::OUT,
                'user_id' => auth()->user()->id,
            ])->whereIn('status', [
                EnumTransactionsStatus::PENDING,
                EnumTransactionsStatus::PROCESSING,
                EnumTransactionsStatus::ABOVELIMIT,
                EnumTransactionsStatus::ERROR,
                EnumTransactionsStatus::AUTHORIZED,
            ])->first();

            if ($pending) {
                throw new \Exception("A transação não pode ser completada. Você já possui um envio pendente na fila, aguarde o processamento.");
            }

            $request['fromAddress'] = $from->address;
            $fee = $this->balanceService->withDrawFee($request);

            $sumValueTransaction = floatval($fee['fee'] + $fee['tax'] + $request->amount);

            if ($request->amount < $this->getValueMinTransaction()) {
                throw new \Exception(trans('messages.transaction.value_below_the_minimum', ['amount' => $this->getValueMinTransaction()]));
            }

            if (!(abs($sumValueTransaction) <= abs($from->balance))) {
                throw new \Exception(trans('messages.transaction.value_exceeds_balance'));
            }

            $gateway = Gateway::where('address', $request->toAddress)->exists();

            if (!$gateway) {
                $valorDiario = $this->getValueByDayUser($from->coin_id, EnumTransactionCategory::TRANSACTION, $fee['is_internal']);
                $valorDiario = floatval($valorDiario);

                if (!$this->_calcLimits($valorDiario, $request->amount, $from->coin_id, $fee['is_internal'], $from->user_id)) {
                    throw new \Exception(trans('messages.transaction.value_exceeds_level_limits'));
                }
            }

            $transaction = Transaction::create([
                'user_id' => $from->user_id,
                'coin_id' => $from->coin_id,
                'wallet_id' => $from->id,
                'toAddress' => $request->toAddress,
                'amount' => $request->amount,
                'status' => EnumTransactionsStatus::PENDING,
                'type' => EnumTransactionType::OUT,
                'category' => EnumTransactionCategory::TRANSACTION,
                'fee' => $fee['fee'],
                'tax' => $fee['tax'],
                'tx' => '',
                'info' => '',
                'error' => '',
                'is_internal' => $fee['is_internal'],
            ]);

            if ($transaction->coin->abbr != "LQX") {
                $this->internalTransaction($transaction);
            }
            $this->internalPayment($transaction);

            $transactionStatus = TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
            ]);

            ActivityLogger::log(trans('messages.transaction.crypto_sent'), $transaction->id, Transaction::class, $transaction);

            $this->balanceService::decrements($transaction);

            DB::commit();
            return response([
                'message' => trans('messages.transaction.sent_success'),
                'transaction' => $transaction,
                'transactionStatus' => $transactionStatus
            ], Response::HTTP_CREATED);

        } catch (\Exception $ex) {
            DB::rollBack();
            return response([
                'message' => $ex->getMessage(). ' => '.$ex->getLine(). ' => '.$ex->getFile()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    private function internalTransaction($transaction)
    {
        $to = UserWallet::where(['address' => $transaction->toAddress, 'coin_id' => $transaction->coin_id, 'type' => EnumUserWalletType::WALLET])->first();
        $isValid = Validations::validEmail($transaction->toAddress);
        if ($isValid) {
            //verificar email interno e pegar wallet
            $user = User::where('email', $transaction->toAddress)->first();
            if (!$user) {
                return;
            }

            $to = UserWallet::where(['user_id' => $user->id, 'coin_id' => $transaction->coin_id, 'type' => EnumUserWalletType::WALLET])->first();
            if (!$to) {
                return;
            }
        }

        if ($to) {

            if (!$this->_checkLimits($transaction)) {
                $transaction->update([
                    'status' => EnumTransactionsStatus::ABOVELIMIT,
                    'toAddress' => $to->address
                ]);

                return false;
            }

            $uuid4 = Uuid::uuid4();
            $internalTx = $uuid4->toString();

            $newTransaction = Transaction::create([
                'user_id' => $to->user_id,
                'sender_user_id' => $transaction->user_id,
                'coin_id' => $to->coin_id,
                'wallet_id' => $to->id,
                'toAddress' => '',
                'amount' => $transaction->amount,
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::TRANSACTION,
                'fee' => 0,
                'taxas' => 0,
                'tx' => $internalTx,
                'info' => trans('info.internal_receiving'),
                'error' => '',
                'is_internal' => true,
            ]);

            TransactionStatus::create([
                'status' => $newTransaction->status,
                'transaction_id' => $newTransaction->id,
            ]);

            $transaction->update(['toAddress' => $to->address, 'status' => EnumTransactionsStatus::SUCCESS, 'tx' => $internalTx, 'info' => trans('info.internal_sent')]);

            $this->balanceService->increments($newTransaction);

            ActivityLogger::log(trans('messages.transaction.crypto_received'), $to->user_id, User::class, $newTransaction);
        }
    }

    public function internalPayment($transaction)
    {
        try {
            $gateway = Gateway::with([
                'user' => function ($user) {
                    return $user->with(['api_key', 'level']);
                },
                'coin'
            ])->where('address', $transaction->toAddress)->first();


            if (!$gateway) {
                return;
            }

            if ($gateway->status != EnumGatewayStatus::NEWW) { //verificar status
                throw new \Exception(trans('messages.gateway.payment_expired'));
            }

            if ($transaction->coin_id != $gateway->coin_id) { //se moeda de envio for a mesma do gateway, processa
                throw new \Exception(trans('messages.gateway.submission_could_not_be_processed'));
            }

            //atualizar o pagamento de acordo com o status
            $status = $this->gatewayService->setStatus($transaction, $gateway->amount);

            $ntx = Uuid::uuid4()->toString();

            $transaction->update([
                'status' => EnumTransactionsStatus::SUCCESS,
                'tx' => $ntx
            ]);

            TransactionStatus::create([
                'status' => EnumTransactionsStatus::SUCCESS,
                'transaction_id' => $transaction->id,
            ]);

            $this->gatewayService->updateInternal($gateway, $transaction);

        } catch
        (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }

    }

    public function getValueMinTransaction()
    {
        $system_config = SysConfig::first();
        return $system_config->send_min_btc;
    }

    protected function revertFee($fee, $coin_id)
    {
        $tax = $this->getTax(auth()->user()->user_level_id, $coin_id);
    }

    public function getTax($user_level_id, $coin_id)
    {
        return TaxCoin::where('user_level_id', '=', $user_level_id)->where('coin_id', '=', $coin_id)->get();
    }

    protected function revertTax($transaction_id)
    {
        return Transaction::find($transaction_id)->taxCoin;
    }

    protected function createTax($transaction)
    {

        $taxCoins = $this->getTax(auth()->user()->user_level_id, $transaction->coin_id);

        foreach ($taxCoins as $taxCoin) {
            TaxCoinTransaction::create([
                'transaction_id' => $transaction->id,
                'tax_coin_id' => $taxCoin->id,
                'crypto' => $taxCoin->value
            ]);
        }
    }

    /**
     * @param mixed $request ->amount
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function verifyBalance(Request $request)
    {
        $from = UserWallet::where(['address' => $request->address, 'user_id' => auth()->user()->id, 'type' => EnumUserWalletType::WALLET])->first();
        $fee = $this->balanceService->withDrawFee($request);

        $valorDiario = $this->getValueByDayUser($from->coin_id, EnumTransactionCategory::TRANSACTION, $fee['is_internal']);
        $valorDiario = floatval($valorDiario);

        $cotacao = $this->conversorService::BRLTAX2BTCMAX($request->amount);
        $taxCoin = $this->taxCoinService->show($from->coin_id, EnumOperations::CRYPTO_SEND, auth()->user()->user_level_id, $request->amount, $cotacao['quote']);

        $sumTaxas = $this->taxCoinService->sumTax($taxCoin);


        $sumValueTransaction = floatval($sumTaxas + $request->amount + $fee);

        if (!((float)$sumValueTransaction <= (float)$from->balance)) {
            return response([
                'message' => trans('messages.transaction.value_exceeds_balance')
            ], Response::HTTP_NOT_ACCEPTABLE);
        }

        if (!$this->_calcLimits($valorDiario, $request->amount, $from->coin_id, $fee['is_internal'], $from->user_id)) {
            return response([
                'message' => trans('messages.transaction.value_exceeds_day_limits')
            ], Response::HTTP_NOT_ACCEPTABLE);
        }
        return response('', Response::HTTP_OK);
    }

    public function getValueByDayUser($coin_id, $category, $is_internal)
    {
        $transactions = Transaction::getValueByDayUser($coin_id, $category, $is_internal);
        $value = 0;

        try {
            foreach ($transactions as $transaction) {
                $value += ($transaction->amount);
            }
        } catch (\Exception $ex) {
            return $value;
        }

        return $value;
    }

    private function _calcLimits($valorDiario, $valorTransaction, $coin_id, $is_internal, $user_id)
    {
        if ($user_id == env("NAVI_USER")) {
            return true;
        }

        $limits = UserLevelLimit::where(
            [
                'user_level_id' => auth()->user()->user_level_id,
                'coin_id' => $coin_id,
                'type' => $is_internal == 1 ? EnumUserLevelLimitType::INTERNAL : EnumUserLevelLimitType::EXTERNAL,
            ]
        )->first();

        $limits->limit = floatval($limits->limit);
        $valorDiario = floatval($valorDiario);

        if ($valorTransaction <= $limits->limit) {
            $_limit = floatval($valorTransaction + $valorDiario);
            if ($_limit <= $limits->limit) {
                return true;
            }
        }
        return false;
    }

    public function estimateFee(Request $request)
    {
        $from = UserWallet::where(['user_id' => auth()->user()->id, 'address' => $request->address, 'type' => EnumUserWalletType::WALLET])->first();
        $request['fromAddress'] = $from->address;
        $fee = $this->balanceService->withDrawFee($request);
        return $fee;
    }

    public function list()
    {
        try {
            $transactions = Transaction::with('coin', 'user_account')
                ->where('user_id', auth()->user()->id)
                ->orderBy('created_at', 'DESC')
                ->paginate(10);

            return response($transactions, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'transactions' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function listByWallet($abbr, $address)
    {
        try {
            $wallet_id = UserWallet::where('address', $address)->firstOrFail();

            $transactions = Transaction::with('coin', 'user_account')
//                ->whereHas('wallet', function ($wallet) use ($address) {
//                    return $wallet->where('address', $address);
//                })
//                ->whereHas('coin', function ($coin) use ($abbr) {
//                    return $coin->where('abbr', $abbr);
//                })
                ->where([
                    'user_id' => auth()->user()->id,
                    'coin_id' => Coin::getByAbbr($abbr)->id,
                    'wallet_id' => $wallet_id->id,
                ])
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
            return response($transactions, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'transactions' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function transfer(TransferRequest $request)
    {
        $amount = (float)$request->amount;

        if (auth()->user()->country_id == 31) {
            if (!$this->balanceService->verifyBalance($amount, 'BRL')) {
                return response(['message' => trans('messages.wallet.insuficient_balance')], Response::HTTP_BAD_REQUEST);
            }
        }

        if (auth()->user()->country_id != 31) {
            if (!$this->balanceService->verifyBalance($amount, 'USD')) {
                return response(['message' => trans('messages.wallet.insuficient_balance')], Response::HTTP_BAD_REQUEST);
            }
        }

        try {

            $beneficiary = User::where('email', $request->email)->firstOrFail();
            $exists = UserFavAccount::where(['user_id' => auth()->user()->id, 'fav_user_id' => $beneficiary->id])->exists();
            if (!$exists) {
                throw new \Exception(trans('messages.account.must_register_email_recepient_first'));
            }

            DB::beginTransaction();

            $uuid = Uuid::uuid4();

            $conversion = $this->convertFiats($amount, $beneficiary);

            $transaction_out = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $conversion['coin_out'],
                'wallet_id' => $conversion['wallet_out'],
                'amount' => $amount,
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::OUT,
                'category' => EnumTransactionCategory::TRANSFER,
                'fee' => 0,
                'tax' => 0,
                'tx' => $uuid->toString(),
                'info' => trans('info.transference_to', ['name' => $beneficiary->name]),
                'error' => '',
                'price' => $conversion['price'],
                'market' => $conversion['market'],
            ]);


            TransactionStatus::create([
                'status' => $transaction_out->status,
                'transaction_id' => $transaction_out->id,
            ]);


            $transaction_in = Transaction::create([
                'user_id' => $beneficiary->id,
                'coin_id' => $conversion['coin_in'],
                'wallet_id' => $conversion['wallet_in'],
                'amount' => $conversion['amount_in'],
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::TRANSFER,
                'fee' => 0,
                'tax' => 0,
                'tx' => $uuid->toString(),
                'info' => trans('info.transference_from', ['name' => auth()->user()->name]),
                'error' => '',
                'price' => $conversion['price'],
                'market' => $conversion['market'],
                'sender_user_id' => auth()->user()->id
            ]);

            TransactionStatus::create([
                'status' => $transaction_in->status,
                'transaction_id' => $transaction_in->id,
            ]);

            $this->balanceService::decrements($transaction_out);
            $this->balanceService::increments($transaction_in);
            DB::commit();
            return response([
                'message' => trans('messages.transaction.sent_success'),
            ], Response::HTTP_CREATED);

        } catch (\Exception $ex) {
            DB::rollBack();
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    private function convertFiats($amount, $user_in)
    {

        if (auth()->user()->country_id == 31) {
            $coin_out = Coin::getByAbbr('BRL')->id;
            $wallet_out = UserWallet::where(['coin_id' => $coin_out, 'user_id' => auth()->user()->id, 'type' => EnumUserWalletType::WALLET])->first()->id;
            //out BRL //in BRL
            if ($user_in->country_id == 31) {
                $conversion = [
                    'coin_out' => $coin_out,
                    'wallet_out' => $wallet_out,
                    'price' => 1,
                    'market' => 1,
                    'coin_in' => $coin_out,
                    'wallet_in' => UserWallet::where(['coin_id' => $coin_out, 'user_id' => $user_in->id, 'type' => EnumUserWalletType::WALLET])->first()->id,
                    'amount_in' => $amount,
                ];
                return $conversion;
            }
            //out BRL //in USD
            if ($user_in->country_id != 31) {
                $coin_out = Coin::getByAbbr('BRL')->id;
                $coin_in = Coin::getByAbbr('USD')->id;
                $dollar = CoinQuote::where(['coin_id' => $coin_in, 'quote_coin_id' => $coin_out])->first()->average_quote;
                $conversion = [
                    'coin_out' => $coin_out,
                    'wallet_out' => $wallet_out,
                    'price' => $dollar,
                    'market' => $dollar,
                    'coin_in' => $coin_in,
                    'wallet_in' => UserWallet::where(['coin_id' => $coin_in, 'user_id' => $user_in->id, 'type' => EnumUserWalletType::WALLET])->first()->id,
                    'amount_in' => $amount / $dollar,
                ];
                return $conversion;
            }
        }

        if (auth()->user()->country_id != 31) {
            $coin_out = Coin::getByAbbr('USD')->id;
            $wallet_out = UserWallet::where(['coin_id' => $coin_out, 'user_id' => auth()->user()->id, 'type' => EnumUserWalletType::WALLET])->first()->id;
            //out USD //in USD
            if ($user_in->country_id != 31) {
                $conversion = [
                    'coin_out' => $coin_out,
                    'wallet_out' => $wallet_out,
                    'price' => 1,
                    'market' => 1,
                    'coin_in' => $coin_out,
                    'wallet_in' => UserWallet::where(['coin_id' => $coin_out, 'user_id' => $user_in->id, 'type' => EnumUserWalletType::WALLET])->first()->id,
                    'amount_in' => $amount,
                ];
                return $conversion;
            }
            //out USD //in BRL
            if ($user_in->country_id == 31) {
                $coin_out = Coin::getByAbbr('USD')->id;
                $coin_in = Coin::getByAbbr('BRL')->id;
                $dollar = CoinQuote::where(['coin_id' => $coin_out, 'quote_coin_id' => $coin_in])->first()->average_quote;
                $conversion = [
                    'coin_out' => $coin_out,
                    'wallet_out' => $wallet_out,
                    'price' => $dollar,
                    'market' => $dollar,
                    'coin_in' => $coin_in,
                    'wallet_in' => UserWallet::where(['coin_id' => $coin_in, 'user_id' => $user_in->id, 'type' => EnumUserWalletType::WALLET])->first()->id,
                    'amount_in' => $amount * $dollar,
                ];
                return $conversion;
            }
        }
    }

    private function _checkLimits($transaction)
    {
        $wallet = UserWallet::findOrFail($transaction->wallet_id);

        if ($wallet->user_id == env("NAVI_USER")) {
            return true;
        }

        $user = User::find($wallet->user_id);
        $limits = UserLevelLimit::where([
            'user_level_id' => $user->user_level_id,
            'coin_id' => $wallet->coin_id,
            'type' => EnumUserLevelLimitType::INTERNAL,
        ])->first();
        $auto = floatval($limits->limit_auto);
        $amount = floatval($transaction->amount);
        return ($auto >= $amount) ? true : false;
    }

}
