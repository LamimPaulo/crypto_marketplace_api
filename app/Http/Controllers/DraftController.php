<?php

namespace App\Http\Controllers;

use App\Enum\EnumCalcType;
use App\Enum\EnumOperations;
use App\Enum\EnumTaxType;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Helpers\ActivityLogger;
use App\Http\Requests\DraftRequest;
use App\Models\Coin;
use App\Models\System\WithdrawalDeadline;
use App\Models\TaxCoin;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserAccount;
use App\Models\User\UserLevel;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use App\Services\ConversorService;
use App\Services\TaxCoinService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class DraftController extends Controller
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

    public function store(DraftRequest $request)
    {
        try {
            $draft = Transaction::where([
                'type' => EnumTransactionType::OUT,
                'category' => EnumTransactionCategory::WITHDRAWAL,
                'user_id' => auth()->user()->id,
            ])
                ->whereIn(
                    'status', [EnumTransactionsStatus::PENDING, EnumTransactionsStatus::PROCESSING]
                )->first();

            if ($draft) {
                throw new \Exception(trans('messages.withdrawal.already_pending'));
            }

            DB::beginTransaction();
            $account = UserAccount::where(['user_id' => auth()->user()->id, 'id' => $request->user_account_id])->first();

            $coin = auth()->user()->country_id == 31 ? Coin::getByAbbr('BRL')->id : Coin::getByAbbr('USD')->id;

            $amount = abs($request->amount);

            if ($amount <= 0) {
                throw new \Exception(trans('messages.transaction.value_must_be_greater_than_zero'));
            }

            $from = UserWallet::where(['user_id' => auth()->user()->id, 'coin_id' => $coin])->first();

            if (!$from) {
                throw new \Exception(trans('messages.wallet.invalid'));
            }

            if (!$from->is_active) {
                throw new \Exception(trans('messages.wallet.inactive'));
            }

            if (!$from->coin->is_active) {
                throw new \Exception(trans('messages.coin.inactive'));
            }

            $fee = $this->estimateTax($request);

            $sumValueTransaction = floatval($fee['fee'] + $fee['tax'] + $amount);

            if (!(abs($sumValueTransaction) <= abs($from->balance))) {
                throw new \Exception(trans('messages.transaction.value_exceeds_balance'));
            }

            $valorDiario = $this->getValueByDayUser($from->coin_id, EnumTransactionCategory::WITHDRAWAL);
            $valorDiario = floatval($valorDiario);

            if ($from->coin_id == 2) {
                if (!$this->_calcBrlLimits($valorDiario, $amount)) {
                    throw new \Exception(trans('messages.transaction.value_exceeds_level_limits'));
                }
            }

            if ($from->coin_id == 3) {
                if (!$this->_calcUsdLimits($valorDiario, $amount)) {
                    throw new \Exception(trans('messages.transaction.value_exceeds_level_limits'));
                }
            }

            $uuid = Uuid::uuid4();
            $transaction = Transaction::create([
                'user_id' => $from->user_id,
                'user_account_id' => $request->user_account_id,
                'coin_id' => $from->coin_id,
                'wallet_id' => $from->id,
                'amount' => $amount,
                'status' => EnumTransactionsStatus::PENDING,
                'type' => EnumTransactionType::OUT,
                'category' => EnumTransactionCategory::WITHDRAWAL,
                'confirmation' => 0,
                'fee' => $fee['fee'],
                'tax' => $fee['tax'],
                'payment_at' => $fee['payment_at'],
                'tx' => $uuid->toString(),
                'info' => '',
                'error' => '',
            ]);

            $transactionStatus = TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
            ]);

            ActivityLogger::log(trans('messages.withdrawal.requested'), $transaction->id, Transaction::class, $transaction);

            $this->balanceService::decrements($transaction);
            DB::commit();
            return response([
                'message' => trans('messages.withdrawal.requested'),
                'transaction' => $transaction,
                'transactionStatus' => $transactionStatus
            ], Response::HTTP_CREATED);

        } catch (\Exception $ex) {
            DB::rollBack();
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function sendBrlCredminer(Request $request)
    {
        try {
            DB::beginTransaction();
            UserAccount::where(['user_id' => auth()->user()->id, 'id' => $request->user_account_id])->first();

            $coin = Coin::getByAbbr('BRL')->id;

            $amount = abs($request->amount);

            if ($amount <= 0) {
                throw new \Exception(trans('messages.transaction.value_must_be_greater_than_zero'));
            }

            $from = UserWallet::where(['user_id' => auth()->user()->id, 'coin_id' => $coin])->first();

            if (!$from) {
                throw new \Exception(trans('messages.wallet.invalid'));
            }

            if (!$from->is_active) {
                throw new \Exception(trans('messages.wallet.inactive'));
            }

            if (!$from->coin->is_active) {
                throw new \Exception(trans('messages.coin.inactive'));
            }

            if (!(abs($amount) <= abs($from->balance))) {
                throw new \Exception(trans('messages.transaction.value_exceeds_balance'));
            }

            $api = new \GuzzleHttp\Client(['http_errors' => false]);

            $response = $api->post('https://api.credminer.com/v1/liquidex/reais', [
                'form_params' => [
                    'login' => $request->toAddress,
                    'real_value' => $request->amount,
                ],
                'headers' => [
                    'Authorization' => 'Bearer XLjYwLEU9G2GsHO5or%87bc093cnIOHdgvi987in8nd98nij2%KIHpTnjW$yVogSUrVd2szJT!LHE@fpHV#ly%xLcJ9FX'
                ]
            ]);

            $statuscode = $response->getStatusCode();

            if (401 === $statuscode) {
                throw new \Exception('Key Inválida.');
            }

            if (422 === $statuscode) {
                throw new \Exception('Login não encontrado.');
            }

            if (200 !== $statuscode && 201 !==$statuscode) {
                throw new \Exception('Erro desconhecido ['.$statuscode.']');
            }

            $result = $response->getBody()->getContents();

            $uuid = Uuid::uuid4();
            $transaction = Transaction::create([
                'user_id' => $from->user_id,
                'user_account_id' => $request->user_account_id,
                'coin_id' => $from->coin_id,
                'wallet_id' => $from->id,
                'amount' => $amount,
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::OUT,
                'category' => EnumTransactionCategory::BRL_SUBMIT,
                'confirmation' => 0,
                'fee' => 0,
                'tax' => 0,
                'payment_at' => Carbon::now(),
                'tx' => $uuid->toString(),
                'info' => "Envio pra Login: '{$request->toAddress}'",
                'error' => '',
            ]);

            $transactionStatus = TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
            ]);

            ActivityLogger::log('R$ Enviado para Credminer: '.$request->toAddress, $transaction->id, Transaction::class, $transaction);

            $this->balanceService::decrements($transaction);
            DB::commit();
            return response([
                'message' => 'R$ Enviado para Credminer: '.$request->toAddress,
                'transaction' => $transaction,
                'transactionStatus' => $transactionStatus
            ], Response::HTTP_CREATED);

        } catch (\Exception $ex) {
            DB::rollBack();
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function cancel(Request $request)
    {
        $request->validate([
            'transaction' => 'required|exists:transactions,id',
        ], [
            'transaction.required' => trans('validation.draft.cancel.transaction_required'),
            'transaction.exists' => trans('validation.draft.cancel.transaction_exists'),
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::where('category', EnumTransactionCategory::WITHDRAWAL)
                ->where('id', $request->transaction)
                ->where('user_id', auth()->user()->id)
                ->where('status', EnumTransactionsStatus::PENDING)
                ->firstOrFail();

            $transaction->status = EnumTransactionsStatus::CANCELED;
            $transaction->save();

            TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
            ]);

            $this->balanceService::reverse($transaction);

            ActivityLogger::log(trans('messages.withdrawal.canceled_by_user'), auth()->user()->id);

            DB::commit();
            return response([
                'message' => trans('messages.withdrawal.canceled'),
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response([
                'status' => 'error',
                'message' => trans('messages.transaction.not_found'),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function estimateTax(DraftRequest $request)
    {
        try {
            $account = UserAccount::where(['user_id' => auth()->user()->id, 'id' => $request->user_account_id])->first();
            $amount = abs($request->amount);

            if ($account->type == 2) {
                return [
                    'amount' => $amount,
                    'fee' => 0,
                    'tax' => 0,
                    'total' => $amount
                ];
            }

            $ted = TaxCoin::where([
                'coin_id' => Coin::getByAbbr('BRL')->id,
                'user_level_id' => auth()->user()->user_level_id,
                'coin_tax_type' => EnumTaxType::TED,
                'operation' => EnumOperations::FIAT_WITHDRAW
            ])->first();

            $tedTax = $ted->value ?? 0;
            if ($ted->calc_type == EnumCalcType::PERCENT) {
                $tedTax = $amount * ($ted->value / 100);
            }

            $deadline = WithdrawalDeadline::findOrFail($request->tax_id);
            $tax = $amount * $deadline->tax / 100;

//            $fee = TaxCoin::where([
//                'coin_id' => Coin::getByAbbr('BRL')->id,
//                'user_level_id' => auth()->user()->user_level_id,
//                'coin_tax_type' => EnumTaxType::OPERACAO,
//                'operation' => EnumOperations::FIAT_WITHDRAW
//            ])->first();
//
//            $feeTax = $fee->value ?? 0;
//            if ($fee->calc_type == EnumCalcType::PERCENT) {
//                $feeTax = $amount * ($fee->value / 100);
//            }

            return [
                'amount' => sprintf("%.2f", $amount),
                'fee' => sprintf("%.2f", $tax),
                'tax' => sprintf("%.2f", $tedTax),
                'total' => sprintf("%.2f", ($amount + $tax + $tedTax)),
                'payment_at' => Carbon::now()->addDays($deadline->deadline)
            ];

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

    }

    public function getValueByDayUser($coin_id, $category)
    {
        $transactions = Transaction::getValueByDayUser($coin_id, $category);
        $value = 0;

        try {
            foreach ($transactions as $transaction) {
                $value += ($transaction->amount + $transaction->fee);
            }
        } catch (\Exception $ex) {
            return $value;
        }
        return $value;
    }

    private function _calcBrlLimits($valorDiario, $valorTransaction)
    {
        $limits = UserLevel::findOrFail(auth()->user()->user_level_id);
        $limits->limit_brl_diary = floatval($limits->limit_brl_diary);
        $valorDiario = floatval($valorDiario);

        if ($valorTransaction <= $limits->limit_brl_diary) {
            $_limit = floatval($valorTransaction + $valorDiario);
            if ($_limit <= $limits->limit_brl_diary) {
                return true;
            }
        }
        return false;
    }

    private function _calcUsdLimits($valorDiario, $valorTransaction)
    {
        $limits = UserLevel::findOrFail(auth()->user()->user_level_id);
        $limits->limit_lqx_diary = floatval($limits->limit_lqx_diary);
        $valorDiario = floatval($valorDiario);

        if ($valorTransaction <= $limits->limit_lqx_diary) {
            $_limit = floatval($valorTransaction + $valorDiario);
            if ($_limit <= $limits->limit_lqx_diary) {
                return true;
            }
        }
        return false;
    }
}
