<?php

namespace App\Http\Controllers\Admin\Mining;

use App\Enum\EnumMiningProfitType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MiningPlanRequest;
use App\Models\Mining\MiningPlan;
use Symfony\Component\HttpFoundation\Response;

class MiningPlanController extends Controller
{
    public function index()
    {
        try {
            $list = MiningPlan::paginate(10);
            return response($list, Response::HTTP_OK);
        } catch (\Exception $ex) {
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function profitTypes()
    {
        return EnumMiningProfitType::TYPE;
    }

    public function store(MiningPlanRequest $request)
    {
        try {
            if ($request->ths_total <= 0) {
                throw new \Exception('A quantidade de THs ser maior que 0.');
            }
            if ($request->ths_quota_price <= 0) {
                throw new \Exception('O preço por THs deve ser maior que 0.');
            }
            if ($request->profit <= 0) {
                throw new \Exception('O valor de comissão deve ser maior que 0.');
            }
            if ($request->profit_payout <= 0) {
                throw new \Exception('O pagamento mínimo deve ser maior que 0.');
            }
            MiningPlan::create($request->all());
            return response([
                'status' => 'success',
                'message' => 'Plano criado com sucesso'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function update(MiningPlanRequest $request)
    {
        try {
            $plan = MiningPlan::findOrFail($request->id);

            if ($request->ths_total <= 0) {
                throw new \Exception('A quantidade de THs ser maior que 0.');
            }
            if ($request->ths_quota_price <= 0) {
                throw new \Exception('O preço por THs deve ser maior que 0.');
            }
            if ($request->profit <= 0) {
                throw new \Exception('O valor de comissão deve ser maior que 0.');
            }
            if ($request->profit_payout <= 0) {
                throw new \Exception('O pagamento mínimo deve ser maior que 0.');
            }

            $plan->update($request->all());
            return response([
                'status' => 'success',
                'message' => 'Plano alterado com sucesso'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

}
