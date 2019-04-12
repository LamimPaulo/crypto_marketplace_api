<?php

namespace App\Http\Controllers\Mining;

use App\Enum\EnumGatewayStatus;
use App\Http\Controllers\Controller;
use App\Models\Mining\MiningPlan;
use App\Models\Mining\MiningQuota;
use App\Models\Mining\MiningQuotaProfit;
use App\Models\Mining\MiningBlock;
use App\Models\Mining\MiningPool;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Gateway;

class MiningPoolController extends Controller
{
    public function userStats()
    {
        try {
            $miningQuota = MiningQuota::where('user_id', auth()->user()->id)->first();
            $miningPlan = MiningPlan::with(['quotas'])->first();
            $miningPool = MiningPool::first();

            $stats['plan_user'] = [
                'ths_quota' => $miningQuota->ths_quota ?? 0
            ];

            $estimate = MiningBlock::whereDate('date_found', Carbon::now()->format('Y-m-d'))->sum('reward');
            $estimate = $estimate * ($miningPlan->profit / 100);

            $stats['reward_estimate'] = sprintf("%.8f", ($estimate / $miningPlan->ths_total) * $stats['plan_user']['ths_quota']);
            $stats['unconfirmed_reward'] = sprintf("%.8f", (($miningPool->unconfirmed_reward * ($miningPlan->profit / 100)) / $miningPlan->ths_total) * $stats['plan_user']['ths_quota']);
            $stats['confirmed_reward'] = sprintf("%.8f", (($miningPool->confirmed_reward * ($miningPlan->profit / 100)) / $miningPlan->ths_total) * $stats['plan_user']['ths_quota']);

            $stats['pool'] = $miningPool;
            $stats['workers_alive'] = $stats['plan_user']['ths_quota'] / 10;
            $stats['workers_dead'] = 0;
            $stats['plan'] = $miningPlan;
            $stats['blocks'] = MiningBlock::where('is_mature', '>=', 0)->orderBy('date_found', 'DESC')->take(6)->get();
            $stats['profit'] = $miningPlan->profit / 100;
            $stats['quotas_remaining'] = $miningPlan->ths_total - $miningPlan->quotas->sum('ths_quota');

            return response($stats, Response::HTTP_OK);

        } catch (\Exception $ex) {
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function userRewardChart()
    {
        $confirmed = MiningQuotaProfit::select(DB::raw('SUM(reward) reward, DATE(updated_at) date'))
            ->where('is_paid', 1)->where('user_id', auth()->user()->id)
            ->groupBy(DB::raw('DATE(updated_at)'))->get();

        $unconfirmed = MiningQuotaProfit::select(DB::raw('SUM(reward) reward, DATE(date_found) date'))
            ->where('user_id', auth()->user()->id)
            ->groupBy(DB::raw('DATE(date_found)'))->get();

        $resultA = [];
        $resultB = [];

        foreach ($unconfirmed as $i => $d) {
            $timestamp = Carbon::parse($d->date)->timestamp;
            $resultA[] =
                [
                    ($timestamp . '000') * 1,
                    sprintf("%.8f", $confirmed[$i]->reward ?? 0) * 1
                ];
            $resultB[] =
                [
                    ($timestamp . '000') * 1,
                    sprintf("%.8f", $d->reward ?? 0) * 1
                ];
        }
        return [
            [
                'type' => 'line',
                'name' => 'À receber',
                'data' => $resultB,
                'color' => '#1b55e2'
            ],
            [
                'type' => 'line',
                'name' => 'Recebido',
                'data' => $resultA,
                'color' => '#24b314'
            ]
        ];
    }

    public function pendingGateway(){
        try {
            $gateway = Gateway::where('mining_user_id', auth()->user()->id)->whereNotIn('status', [EnumGatewayStatus::PAID, EnumGatewayStatus::EXPIRED]);

            return response([
             'status' => 'success',
             'payment' => $gateway->first(),
             'count' => $gateway->count(),
            ], Response::HTTP_OK);

        } catch (\Exception $ex) {
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
