<?php

namespace App\Http\Controllers\Admin\Mining;

use App\Http\Controllers\Controller;
use App\Models\Mining\MiningPlan;
use App\Models\Mining\MiningQuota;
use App\Models\Mining\MiningBlock;
use App\Models\Mining\MiningPool;
use App\Models\Mining\MiningWorker;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class MiningPoolController extends Controller
{
    public function stats()
    {
        try {
            $stats['pool'] = MiningPool::first();
            $stats['plan'] = MiningPlan::first();
            $stats['workers_alive'] = MiningWorker::where('alive', true)->count();
            $estimate = MiningBlock::whereDate('date_found', Carbon::now()->format('Y-m-d'))->sum('reward');
            $stats['reward_estimate'] = sprintf("%.8f", $estimate);
            $stats['workers_dead'] = MiningWorker::where('alive', false)->count();
            $stats['blocks'] = MiningBlock::where('is_mature', '>=', 0)->where('confirmations', '<', 100)->orderBy('date_found', 'DESC')->take(13)->get();

            return response($stats, Response::HTTP_OK);

        } catch (\Exception $ex) {
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function rewardChart()
    {
        $confirmed = MiningBlock::select(DB::raw('SUM(reward) reward, DATE(date_found) date'))
            ->where('is_mature', 1)
            ->groupBy(DB::raw('DATE(date_found)'))->get()->makeHidden(['diff']);
        $unconfirmed = MiningBlock::select(DB::raw('SUM(reward) reward, DATE(date_found) date'))
            ->groupBy(DB::raw('DATE(date_found)'))->get()->makeHidden(['diff']);
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
                'name' => 'Não Confirmado',
                'data' => $resultB,
                'color' => '#1b55e2'
            ],
            [
                'type' => 'line',
                'name' => 'Confirmado',
                'data' => $resultA,
                'color' => '#24b314'
            ]
        ];
    }

    public function refresh()
    {
        Artisan::call("pool:refresh");
    }

    public function quotasList()
    {
        try {
            $list = MiningQuota::with(['user','plan'])->orderBy('updated_at', 'DESC')->paginate(10);
            return response($list, Response::HTTP_OK);
        } catch (\Exception $ex) {
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function miniReport()
    {
        try {
            $users = MiningQuota::all()->count();
            $miningPlan = MiningPlan::with(['quotas'])->first();
            $ths_quotas = $miningPlan->quotas->sum('ths_quota');
            return response([
                'ths_total' => $miningPlan->ths_total * 1,
                'ths_quotas' => $ths_quotas,
                'ths_amount' => $ths_quotas * $miningPlan->ths_quota_price,
                'users' => $users,
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
