<?php

namespace App\Http\Controllers\Credminer;

use App\Enum\EnumNanotechOperationStatus;
use App\Enum\EnumNanotechOperationType;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Http\Controllers\Controller;
use App\Http\Requests\NanotechInfoRequest;
use App\Http\Requests\NanotechRequest;
use App\Models\Nanotech\Nanotech;
use App\Models\Nanotech\NanotechOperation;
use App\Models\Nanotech\NanotechProfitPercent;
use App\Models\Nanotech\NanotechType;
use App\Models\Transaction;
use App\Models\User\UserLevel;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use App\Services\ConversorService;
use App\User;
use Illuminate\Http\Request;
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


    public function index()
    {
        $types = NanotechType::with('coin')->get();
        $return = [];
        foreach ($types as $type) {
            $return[] = [
                'type' => $type->id,
                'desc' => $type->type,
                'coin' => $type->coin->abbr,
                'montly_return' => $type->montly_return
            ];
        }

        try {
            return response([
                'status' => 'success',
                'types' => $return
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return \response([
                'status' => 'error',
                'message' => $e->getMessage() . '(' . $e->getLine()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function info(NanotechInfoRequest $request)
    {
        try {
            $user = User::where('api_key', '=', $request->api_key)->first();
            $type = NanotechType::with('coin')->findOrFail($request->type);

            return [
                'average_profits' => $this->returnPercentage($request->type),
                'brokerage_fee' => $this->brokerageFee($request->type, $user->user_level_id),
                'under_managment' => $this->total($request->type),
                'user_investment' => $this->start($request->type, $user->id),
                'user_profit' => $this->profit($request->type, $user->id),
                'total_user_investment' => $this->totalSum($request->type, $user->id),
                'coin' => $type->coin->abbr
            ];
        } catch (\Exception $e) {
            return \response([
                'status' => 'error',
                'message' => $e->getMessage() . '(' . $e->getLine()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function invest(NanotechRequest $request)
    {
        $user = User::where('api_key', '=', $request->api_key)->first();
        $type = NanotechType::findOrFail($request->type);

        if (!is_numeric($request->amount) OR $request->amount <= 0) {
            return response(['message' => trans('messages.invalid_value_sent')], Response::HTTP_BAD_REQUEST);
        }

        if ($request->operation_type != EnumNanotechOperationType::IN AND $request->operation_type != EnumNanotechOperationType::PROFIT_IN) {
            return response(['message' => trans('messages.general.invalid_operation_type')], Response::HTTP_BAD_REQUEST);
        }

        //verificar se o usuario ja possui investimento
        $investment = $this->checkNanotech($user->id, $type->id, $type->coin_id);
        if (!$investment) {
            return response(['message' => trans('messages.products.error_creating_investment')], Response::HTTP_BAD_REQUEST);
        }

        try {
            $brokerageFeePercentage = $this->brokerageFee($type->id, $user->user_level_id);

            $brokerageFee = $request->amount * $brokerageFeePercentage / 100;
            $amount = $request->amount - $brokerageFee;
            $operation = [
                'user_id' => $user->id,
                'investment_id' => $investment,
                'amount' => EnumNanotechOperationType::PROFIT_IN == $request->operation_type ? $request->amount : $amount,
                'brokerage_fee_percentage' => EnumNanotechOperationType::PROFIT_IN == $request->operation_type ? 0 : $brokerageFeePercentage,
                'status' => EnumNanotechOperationStatus::SUCCESS,
                'type' => EnumNanotechOperationType::IN,
                'brokerage_fee' => EnumNanotechOperationType::PROFIT_IN == $request->operation_type ? 0 : $brokerageFee
            ];

            if (EnumNanotechOperationType::IN == $request->operation_type) {
                //verificar se possui saldo suficiente para deposito
                $wallet = UserWallet::where(['user_id' => $user->id, 'coin_id' => $type->coin_id, 'type' => EnumUserWalletType::WALLET])->first();
                //criar transaction
                Transaction::create([
                    'user_id' => $wallet->user_id,
                    'coin_id' => $wallet->coin_id,
                    'wallet_id' => $wallet->id,
                    'toAddress' => '',
                    'amount' => $operation['amount'],
                    'status' => EnumTransactionsStatus::SUCCESS,
                    'type' => EnumTransactionType::OUT,
                    'category' => $type->id== 3 ? EnumTransactionCategory::MASTERNODE_CREDMINER : EnumTransactionCategory::NANOTECH_CREDMINER,
                    'fee' => 0,
                    'tax' => $operation['brokerage_fee'],
                    'tx' => Uuid::uuid4()->toString(),
                    'info' => trans('info.arbitrage_investment'),
                    'error' => '',
                ]);

            }

            if (EnumNanotechOperationType::PROFIT_IN == $request->operation_type) {
                //verificar se o valor de lucro é valido
                $profit = $this->profit($request->type, $user->id);
                if ($request->amount > $profit) {
                    throw new \Exception(trans('messages.products.insuficient_profit'));
                }

                NanotechOperation::create([
                    'user_id' => $user->id,
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

            return response(['message' => trans('messages.products.investment_success')], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    private function brokerageFee($type, $user_level)
    {
        try {
            $fee = UserLevel::where('id', $user_level)->first();
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

    private function returnPercentage($type)
    {
        try {
            $investmentType = NanotechType::findOrFail($type);

            $montly = NanotechProfitPercent::where('day', '>=', date('Y-m-28', strtotime("-1 month")))
                ->where('day', '<=', date('Y-m-t'))
                ->where('type_id', $investmentType->id)
                ->sum('percent');

            $diary = NanotechProfitPercent::where('day', date('Y-m-d'))->first();

            return [
                'base' => sprintf("%.2f", $investmentType->montly_return - 0),
                'current_month' => sprintf("%.2f", $montly),
                'current_day' => sprintf("%.3f", $diary->percent)
            ];

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function total($type)
    {
        try {
            $investmentType = NanotechType::findOrFail($type);
            $investments = Nanotech::where('type_id', $investmentType->id);
            return (string)sprintf("%.8f", $investments->sum('amount'));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function start($type, $user_id)
    {
        try {
            $investmentType = NanotechType::findOrFail($type);
            $investments = Nanotech::where('user_id', $user_id)
                ->where('type_id', $investmentType->id)
                ->get();

            return sprintf("%.8f", $investments->sum('amount'));

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function profit($type, $user_id)
    {
        try {

            $investment = Nanotech::where('type_id', $type)
                ->where('user_id', $user_id)->first();

            if (!$investment) {
                return 0;
            }

            $operation = NanotechOperation::whereIn('type',
                [EnumNanotechOperationType::PROFIT, EnumNanotechOperationType::PROFIT_WITHDRAWAL, EnumNanotechOperationType::PROFIT_IN])
                ->where('user_id', $user_id)
                ->where('investment_id', $investment->id);

            return (string)sprintf("%.8f", $operation->sum('amount'));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function totalSum($type, $user_id)
    {
        try {
            return sprintf("%.8f", $this->profit($type, $user_id) + $this->start($type, $user_id));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function checkNanotech($user, $type, $coin)
    {
        $investment = Nanotech::where([
            'user_id' => $user,
            'coin_id' => $coin,
            'type_id' => $type,
        ])->first();

        if (!$investment) {
            $investment = Nanotech::create([
                'user_id' => $user,
                'coin_id' => $coin,
                'type_id' => $type,
                'amount' => 0,
                'status' => 1
            ]);
        }
        return $investment->id;
    }


}
