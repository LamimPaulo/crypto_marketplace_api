<?php

namespace App\Http\Controllers\Investments;

use App\Enum\EnumInvestmentOperationStatus;
use App\Enum\EnumInvestmentOperationType;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Http\Controllers\Controller;
use App\Http\Requests\InvestmentRequest;
use App\Models\Coin;
use App\Models\Investments\Investment;
use App\Models\Investments\InvestmentOperation;
use App\Models\Investments\InvestmentProfitPercent;
use App\Models\Investments\InvestmentType;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserLevel;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use App\Services\ConversorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class InvestmentController extends Controller
{
    protected $balanceService;
    protected $conversorService;

    public function __construct(
        BalanceService $balance,
        ConversorService $conversor
    )
    {
        $this->conversorService = $conversor;
        $this->balanceService = $balance;
    }


    public function index($type)
    {
        try {
            return [
                'average_profits' => $this->returnPercentage($type),
                'brokerage_fee' => $this->brokerageFee(),
                'under_managment' => $this->total($type),
                'user_investment' => $this->start($type),
                'user_profit' => $this->profit($type),
                'total_user_investment' => $this->totalSum($type),
                'coin' => 'BTC'
            ];
        } catch (\Exception $e) {
            return \response([
                'status' => 'error',
                'message' => $e->getMessage() . '(' . $e->getLine()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function brokerageFee()
    {
        try {
            $fee = UserLevel::where('id', auth()->user()->user_level_id)->first();
            return round($fee->brokerage_fee);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function returnPercentage($type)
    {
        try {
            $investmentType = InvestmentType::findOrFail($type);

            $montly = InvestmentProfitPercent::where('day', '>=', date('Y-m-28', strtotime("-1 month")))
                ->where('day', '<=', date('Y-m-t'))
                ->where('type_id', $investmentType->id)
                ->sum('percent');

            $diary = InvestmentProfitPercent::where('day', date('Y-m-d'))->first();

            return [
                'base' => sprintf("%.2f", $investmentType->montly_return - 0),
                'current_month' => sprintf("%.2f", $montly),
                'current_day' => sprintf("%.3f", $diary->percent)
            ];

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function total($type)
    {
        try {
            $investmentType = InvestmentType::findOrFail($type);
            $investments = Investment::where('type_id', $investmentType->id);
            return (string)sprintf("%.8f", $investments->sum('amount'));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function start($type)
    {
        try {
            $investmentType = InvestmentType::findOrFail($type);
            $investments = Investment::where('user_id', auth()->user()->id)
                ->where('type_id', $investmentType->id)
                ->get();

            return sprintf("%.8f", $investments->sum('amount'));

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function profit($type)
    {
        try {

            $investment = Investment::where('type_id', $type)
                ->where('user_id', auth()->user()->id)
                ->where('coin_id', 1)->first();

            if (!$investment) {
                return 0;
            }

            $operation = InvestmentOperation::whereIn('type',
                [EnumInvestmentOperationType::PROFIT, EnumInvestmentOperationType::PROFIT_DRAFT, EnumInvestmentOperationType::PROFIT_IN])
                ->where('user_id', auth()->user()->id)
                ->where('investment_id', $investment->id);

            return (string)sprintf("%.8f", $operation->sum('amount'));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function totalSum($type)
    {
        try {
            return sprintf("%.8f", $this->profit($type) + $this->start($type));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function chart()
    {
    }

    /**
     * @param InvestmentRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function send(InvestmentRequest $request)
    {
        if (!is_numeric($request->amount) OR $request->amount <= 0) {
            return response(['message' => trans('messages.invalid_value_sent')], Response::HTTP_BAD_REQUEST);
        }

        if ($request->operation_type != EnumInvestmentOperationType::IN AND $request->operation_type != EnumInvestmentOperationType::PROFIT_IN) {
            return response(['message' => trans('messages.general.invalid_operation_type')], Response::HTTP_BAD_REQUEST);
        }

        //verificar se o usuario ja possui investimento
        $investment = $this->checkInvestment(auth()->user()->id, $request->type, 1);
        if (!$investment) {
            return response(['message' => trans('messages.products.error_creating_investment')], Response::HTTP_BAD_REQUEST);
        }

        try {
            $brokerageFeePercentage = $this->brokerageFee();
            $request['amount'] = $this->_calc_amount($request->amount, $request->coin);

            $brokerageFee = $request->amount * $brokerageFeePercentage / 100;
            $amount = $request->amount - $brokerageFee;
            $operation = [
                'user_id' => auth()->user()->id,
                'investment_id' => $investment,
                'amount' => $amount,
                'brokerage_fee_percentage' => $brokerageFeePercentage,
                'status' => EnumInvestmentOperationStatus::SUCCESS,
                'type' => EnumInvestmentOperationType::IN,
                'brokerage_fee' => $brokerageFee
            ];

            if (EnumInvestmentOperationType::IN == $request->operation_type) {
                //verificar se possui saldo suficiente para deposito
                $wallet = UserWallet::where(['user_id' => auth()->user()->id, 'coin_id' => 1, 'type' => EnumUserWalletType::WALLET])->first();
                if ($wallet->balance < $request->amount) {
                    throw new \Exception(trans('messages.wallet.insuficient_balance'));
                }

                //criar transaction e decrementar balance
                $transaction = Transaction::create([
                    'user_id' => $wallet->user_id,
                    'coin_id' => $wallet->coin_id,
                    'wallet_id' => $wallet->id,
                    'toAddress' => '',
                    'amount' => $operation['amount'],
                    'status' => EnumTransactionsStatus::SUCCESS,
                    'type' => EnumTransactionType::OUT,
                    'category' => EnumTransactionCategory::ARBITRAGE,
                    'fee' => 0,
                    'tax' => $operation['brokerage_fee'],
                    'tx' => Uuid::uuid4()->toString(),
                    'info' => trans('info.arbitrage_investment'),
                    'error' => '',
                ]);

                $this->balanceService::decrements($transaction);
            }

            if (EnumInvestmentOperationType::PROFIT_IN == $request->operation_type) {
                if ($request->coin != 1) {
                    throw new \Exception(trans('messages.coin.not_compatible_with_investment'));
                }
                //verificar se o valor de lucro é valido
                $profit = $this->profit($request->type);
                if ($request->amount > $profit) {
                    throw new \Exception(trans('messages.products.insuficient_profit'));
                }

                InvestmentOperation::create([
                    'user_id' => auth()->user()->id,
                    'investment_id' => $investment,
                    'amount' => 0 - $request->amount,
                    'status' => EnumInvestmentOperationStatus::SUCCESS,
                    'type' => EnumInvestmentOperationType::PROFIT_IN,
                ]);
            }

            //increment investment
            $operation = InvestmentOperation::create($operation);
            $operation->type_id = $request->type;
            $operation->coin_id = 1;

            Investment::increments($operation);

            return response(['message' => trans('messages.products.investment_success')], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param $amount
     * @param $coin
     * @return bool
     * @throws \Exception
     */
    private function _calc_amount($amount, $coin)
    {
        if ($coin == 1) {
            return $amount;
        }

        $request['amount'] = $amount;
        $request['quote'] = 'BTC';

        if ($coin == 2 AND auth()->user()->country_id == 31) {
            if (!$this->balanceService->verifyBalance($amount, 'BRL')) {
                throw new \Exception(trans('messages.wallet.insuficient_balance'));
            }

            $request['base'] = 'BRL';
            $result = $this->conversorService::BRL2BTCSMAX($amount);
            return $this->convertCoin($request, $result);
        }

        if ($coin == 3 AND auth()->user()->country_id != 31) {
            if (!$this->balanceService->verifyBalance($amount, 'USD')) {
                return response(['message' => 'Insufficient balance to complete the operation'], Response::HTTP_BAD_REQUEST);
            }

            $result = $this->conversorService::USD2BTCMAX($amount);
            $request['base'] = 'USD';
            return $this->convertCoin($request, $result);
        }

        throw new \Exception(trans('messages.coin.not_compatible_with_investment'));
    }

    /**
     * @param Request $request
     * @param float amount
     * @param type EnumInvestmentOperationType
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function draft(Request $request)
    {
        if ($request->operation_type != EnumInvestmentOperationType::DRAFT_TOTAL) {
            if (!is_numeric($request->amount) OR $request->amount <= 0) {
                return response(['message' => trans('messages.invalid_value_request')], Response::HTTP_BAD_REQUEST);
            }
        }

        if ($request->operation_type != EnumInvestmentOperationType::DRAFT
            AND $request->operation_type != EnumInvestmentOperationType::PROFIT_DRAFT
            AND $request->operation_type != EnumInvestmentOperationType::DRAFT_TOTAL) {
            return response(['message' => trans('messages.general.invalid_operation_type')], Response::HTTP_BAD_REQUEST);
        }

        //verificar se o usuario ja possui investimento
        $investment = $this->checkInvestment(auth()->user()->id, $request->type, 1);
        if (!$investment) {
            return response(['message' => trans('messages.products.error_creating_investment')], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $operation = [
                'user_id' => auth()->user()->id,
                'amount' => 0 - $request->amount,
                'investment_id' => $investment,
                'status' => EnumInvestmentOperationStatus::PENDING,
                'type' => $request->operation_type,
            ];

            if (EnumInvestmentOperationType::DRAFT == $request->operation_type) {
                //verificar se possui saldo suficiente para saque
                $investmentBalance = $this->start($request->type);
                if ($request->amount > $investmentBalance) {
                    throw new \Exception(trans('messages.products.insuficient_investment_balance'));
                }
                //solicitar saque de acordo com valor digitado
                $operation = InvestmentOperation::create($operation);
                $operation->type_id = $request->type;
                $operation->coin_id = 1;
                Investment::increments($operation);
            }

            if (EnumInvestmentOperationType::PROFIT_DRAFT == $request->operation_type) {
                //verificar se possui lucro suficiente para saque
                $profit = $this->profit($request->type);
                if ($request->amount > $profit) {
                    throw new \Exception(trans('messages.products.insuficient_profit'));
                }
                //solicitar saque do lucro de acordo com o valor digitado
                InvestmentOperation::create($operation);
            }

            if (EnumInvestmentOperationType::DRAFT_TOTAL == $request->operation_type) {
                //solicitar saque do investimento total
                $operation = InvestmentOperation::create([
                    'user_id' => auth()->user()->id,
                    'coin_id' => 1,
                    'investment_id' => $investment,
                    'amount' => 0 - $this->start($request->type),
                    'status' => EnumInvestmentOperationStatus::PENDING,
                    'type' => EnumInvestmentOperationType::DRAFT,
                ]);

                //solicitar saque do lucro total
                InvestmentOperation::create([
                    'user_id' => auth()->user()->id,
                    'coin_id' => 1,
                    'investment_id' => $investment,
                    'amount' => 0 - $this->profit($request->type),
                    'status' => EnumInvestmentOperationStatus::PENDING,
                    'type' => EnumInvestmentOperationType::PROFIT_DRAFT,
                ]);
                $operation->type_id = $request->type;
                $operation->coin_id = 1;
                Investment::increments($operation);
            }

            DB::commit();
            return response(['message' => trans('messages.withdrawal.success')], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => $e->getMessage() . " ({$e->getLine()})"], Response::HTTP_BAD_REQUEST);
        }
    }

    private function checkInvestment($id, $type, $coin)
    {
        $investment = Investment::where([
            'user_id' => $id,
            'coin_id' => $coin,
            'type_id' => $type,
        ])->first();

        if (!$investment) {
            $investment = Investment::create([
                'user_id' => $id,
                'coin_id' => $coin,
                'type_id' => $type,
                'amount' => 0,
                'status' => 1
            ]);
        }
        return $investment->id;
    }

    private function convertCoin($request, $result)
    {
        try {
            $uuid = Uuid::uuid4();
            $coin_out = Coin::getByAbbr($request['base'])->id;
            $coin_in = Coin::getByAbbr($request['quote'])->id;

            $wallet_out = UserWallet::where(['coin_id' => $coin_out, 'user_id' => auth()->user()->id, 'type' => EnumUserWalletType::WALLET])->first()->id;
            $wallet_in = UserWallet::where(['coin_id' => $coin_in, 'user_id' => auth()->user()->id, 'type' => EnumUserWalletType::WALLET])->first()->id;

            $transaction_out = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $coin_out,
                'wallet_id' => $wallet_out,
                'amount' => $request['amount'],
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::OUT,
                'category' => EnumTransactionCategory::CONVERSION,
                'fee' => 0,
                'tax' => 0,
                'tx' => $uuid->toString(),
                'info' => trans('info.automatic_investment_conversion'),
                'error' => '',
                'price' => $result['current'],
                'market' => $result['quote']
            ]);

            TransactionStatus::create([
                'status' => $transaction_out->status,
                'transaction_id' => $transaction_out->id,
            ]);

            $this->balanceService::decrements($transaction_out);

            $transaction_in = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $coin_in,
                'wallet_id' => $wallet_in,
                'amount' => $result['amount'],
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::CONVERSION,
                'fee' => 0,
                'tax' => 0,
                'tx' => $uuid->toString(),
                'info' => trans('info.automatic_investment_conversion'),
                'error' => '',
                'price' => $result['current'],
                'market' => $result['quote']
            ]);

            TransactionStatus::create([
                'status' => $transaction_in->status,
                'transaction_id' => $transaction_in->id,
            ]);

            $this->balanceService::increments($transaction_in);
            return $result['amount'];

        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
}
