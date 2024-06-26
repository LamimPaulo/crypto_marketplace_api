<?php

namespace App\Http\Controllers\Nanotech;

use App\Enum\EnumNanotechOperationStatus;
use App\Enum\EnumNanotechOperationType;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Http\Controllers\Controller;
use App\Http\Requests\NanotechRequest;
use App\Models\Coin;
use App\Models\Nanotech\Nanotech;
use App\Models\Nanotech\NanotechOperation;
use App\Models\Nanotech\NanotechProfitPercent;
use App\Models\Nanotech\NanotechType;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserLevel;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use App\Services\ConversorService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class NanotechController extends Controller
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
            $type = NanotechType::with('coin')->findOrFail($type);
            return [
                'average_profits' => $this->returnPercentage($type->id),
                'brokerage_fee' => $this->brokerageFee($type->id),
                'under_managment' => $this->total($type->id),
                'user_investment' => $this->start($type->id),
                'user_profit' => $this->profit($type->id),
                'total_user_investment' => $this->totalSum($type->id),
                'coin' => $type->coin->abbr,
                'chart_data' => $this->chart($type->id),
                'name' => $type->type
            ];
        } catch (\Exception $e) {
            return \response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function brokerageFee($type)
    {
        try {
            $fee = UserLevel::where('id', auth()->user()->user_level_id)->first();
            switch ($type) {
                case (1):
                    return $fee->nanotech_btc_fee;
                    break;
                case (2):
                    return $fee->nanotech_lqx_fee;
                    break;
                default:
                    return $fee->masternode_fee;
                    break;
            }

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function returnPercentage($type)
    {
        try {
            $investmentType = NanotechType::findOrFail($type);

            $montly = NanotechProfitPercent::where('day', '>=', date('Y-m-28', strtotime("-1 month")))
                ->where('day', '<=', date('Y-m-t'))
                ->where('type_id', $investmentType->id)
                ->sum('percent');

            $diary = NanotechProfitPercent::where([
                'day' => date('Y-m-d'),
                'type_id' => $investmentType->id
            ])->first();

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
            $investmentType = NanotechType::findOrFail($type);
            $investments = Nanotech::where('type_id', $investmentType->id);
            return (string)sprintf("%.8f", $investments->sum('amount'));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function start($type)
    {
        try {
            $investmentType = NanotechType::findOrFail($type);
            $investments = Nanotech::where('user_id', auth()->user()->id)
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

            $investment = Nanotech::where('type_id', $type)
                ->where('user_id', auth()->user()->id)
                ->first();

            if (!$investment) {
                return 0;
            }

            $operation = NanotechOperation::whereIn('type',
                [EnumNanotechOperationType::PROFIT, EnumNanotechOperationType::PROFIT_WITHDRAWAL, EnumNanotechOperationType::PROFIT_IN])
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

    public function chart($type)
    {
        $profits = NanotechProfitPercent::select("percent", "day")
            ->where('day', '>', Carbon::now()->subMonths(1))
            ->where('day', '<', Carbon::now())
            ->where('type_id', $type)
            ->orderBy('day')
            ->get();

        $chart = [];

        foreach ($profits as $profit) {
            $chart[] = [
                $profit->day,
                $profit->percent
            ];
        }
        return $chart;
    }

    /**
     * @param NanotechRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function send(NanotechRequest $request)
    {
        if (!is_numeric($request->amount) OR $request->amount <= 0) {
            return response(['message' => trans('messages.invalid_value_sent')], Response::HTTP_BAD_REQUEST);
        }

        if ($request->operation_type != EnumNanotechOperationType::IN AND $request->operation_type != EnumNanotechOperationType::PROFIT_IN) {
            return response(['message' => trans('messages.general.invalid_operation_type')], Response::HTTP_BAD_REQUEST);
        }

        $type = NanotechType::findOrFail(1);
        //verificar se o usuario ja possui investimento
        $investment = $this->checkNanotech(auth()->user()->id, $type->id, $type->coin_id);
        if (!$investment) {
            return response(['message' => trans('messages.products.error_creating_investment')], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();
            $brokerageFeePercentage = $this->brokerageFee($type->id);
            $brokerageFee = $request->amount * $brokerageFeePercentage / 100;
            $amount = $request->amount - $brokerageFee;

            $operation = [
                'user_id' => auth()->user()->id,
                'investment_id' => $investment,
                'amount' => $amount,
                'brokerage_fee_percentage' => $brokerageFeePercentage,
                'status' => EnumNanotechOperationStatus::SUCCESS,
                'type' => EnumNanotechOperationType::IN,
                'brokerage_fee' => $brokerageFee
            ];

            if (EnumNanotechOperationType::IN == $request->operation_type) {
                //verificar se possui saldo suficiente para deposito
                $wallet = UserWallet::where([
                    'user_id' => auth()->user()->id,
                    'coin_id' => $type->coin_id,
                    'type' => EnumUserWalletType::WALLET,
                ])->first();

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
                    'category' => EnumTransactionCategory::NANOTECH,
                    'fee' => 0,
                    'tax' => $operation['brokerage_fee'],
                    'tx' => Uuid::uuid4()->toString(),
                    'info' => trans('info.arbitrage_investment'),
                    'error' => '',
                ]);

                $this->balanceService::decrements($transaction);
            }

            if (EnumNanotechOperationType::PROFIT_IN == $request->operation_type) {
                //verificar se o valor de lucro é valido
                $profit = $this->profit($type->id);
                if ($request->amount > $profit) {
                    throw new \Exception(trans('messages.products.insuficient_profit'));
                }

                NanotechOperation::create([
                    'user_id' => auth()->user()->id,
                    'investment_id' => $investment,
                    'amount' => 0 - $request->amount,
                    'status' => EnumNanotechOperationStatus::SUCCESS,
                    'type' => EnumNanotechOperationType::PROFIT_IN,
                ]);
            }

            //increment investment
            $operation = NanotechOperation::create($operation);
            $operation->type_id = $type->id;
            $operation->coin_id = $type->coin_id;

            Nanotech::increments($operation);
            DB::commit();
            return response(['message' => trans('messages.products.investment_success')], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param Request $request
     * @param float amount
     * @param type EnumNanotechOperationType
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function withdrawal(Request $request)
    {
        $type = NanotechType::findOrFail(1);

//        if($type->coin_id==10){
//                return response(['message' => 'Retiradas Nanotech LQX só podem ser efetuadas 60 dias após investimento inicial.'], Response::HTTP_BAD_REQUEST);
//        }

        if ($request->operation_type != EnumNanotechOperationType::TOTAL_WITHDRAWAL) {
            if (!is_numeric($request->amount) OR $request->amount <= 0) {
                return response(['message' => trans('messages.invalid_value_request')], Response::HTTP_BAD_REQUEST);
            }
        }

        if ($request->operation_type != EnumNanotechOperationType::WITHDRAWAL
            AND $request->operation_type != EnumNanotechOperationType::PROFIT_WITHDRAWAL
            AND $request->operation_type != EnumNanotechOperationType::TOTAL_WITHDRAWAL) {
            return response(['message' => trans('messages.general.invalid_operation_type')], Response::HTTP_BAD_REQUEST);
        }

        //verificar se o usuario ja possui investimento
        $investment = $this->checkNanotech(auth()->user()->id, $type->id, $type->coin_id);
        if (!$investment) {
            return response(['message' => trans('messages.products.error_creating_investment')], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $operation = [
                'user_id' => auth()->user()->id,
                'amount' => 0 - $request->amount,
                'investment_id' => $investment,
                'status' => EnumNanotechOperationStatus::PENDING,
                'type' => $request->operation_type,
            ];

            if (EnumNanotechOperationType::WITHDRAWAL == $request->operation_type) {
                //verificar se possui saldo suficiente para saque
                $investmentBalance = $this->start(1);
                if ($request->amount > $investmentBalance) {
                    throw new \Exception(trans('messages.products.insuficient_investment_balance'));
                }
                //solicitar saque de acordo com valor digitado
                $operation = NanotechOperation::create($operation);
                $operation->type_id = $type->id;
                $operation->coin_id = $type->coin_id;
                Nanotech::increments($operation);
            }

            if (EnumNanotechOperationType::PROFIT_WITHDRAWAL == $request->operation_type) {
                //verificar se possui lucro suficiente para saque
                $profit = $this->profit(1);
                if ($request->amount > $profit) {
                    throw new \Exception(trans('messages.products.insuficient_profit'));
                }
                //solicitar saque do lucro de acordo com o valor digitado
                NanotechOperation::create($operation);
            }

            if (EnumNanotechOperationType::TOTAL_WITHDRAWAL == $request->operation_type) {
                //solicitar saque do investimento total
                $operation = NanotechOperation::create([
                    'user_id' => auth()->user()->id,
                    'coin_id' => $type->coin_id,
                    'investment_id' => $investment,
                    'amount' => 0 - $this->start(1),
                    'status' => EnumNanotechOperationStatus::PENDING,
                    'type' => EnumNanotechOperationType::WITHDRAWAL,
                ]);

                //solicitar saque do lucro total
                NanotechOperation::create([
                    'user_id' => auth()->user()->id,
                    'coin_id' => $type->coin_id,
                    'investment_id' => $investment,
                    'amount' => 0 - $this->profit(1),
                    'status' => EnumNanotechOperationStatus::PENDING,
                    'type' => EnumNanotechOperationType::PROFIT_WITHDRAWAL,
                ]);
                $operation->type_id = 1;
                $operation->coin_id = $type->coin_id;
                Nanotech::increments($operation);
            }

            DB::commit();
            return response(['message' => trans('messages.withdrawal.success')], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => $e->getMessage() . " ({$e->getLine()})"], Response::HTTP_BAD_REQUEST);
        }
    }

    private function checkNanotech($id, $type, $coin)
    {
        $investment = Nanotech::where([
            'user_id' => $id,
            'coin_id' => $coin,
            'type_id' => $type,
        ])->first();

        if (!$investment) {
            $investment = Nanotech::create([
                'user_id' => $id,
                'coin_id' => $coin,
                'type_id' => $type,
                'amount' => 0,
                'status' => 1
            ]);
        }
        return $investment->id;
    }
}
