<?php

namespace App\Http\Controllers\Mining;

use App\Http\Controllers\Controller;
use App\Models\Mining\MiningBlock;
use App\Models\Mining\MiningPool;
use App\Models\Mining\MiningWorker;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class MiningPoolController extends Controller
{
    public function status()
    {
        try {

            $stats['pool'] = MiningPool::first();
            $stats['workers_dead']  = rand(1,50);
            $stats['workers_alive'] = 17000 - $stats['workers_dead'];
            $stats['pool']['hashrate'] = $stats['workers_alive'] * 12.5;
            $stats['pool']['ths_multi'] = env("SLUSH_MULTI");
            $estimate = MiningBlock::whereDate('date_found', Carbon::now()->format('Y-m-d'))->sum('reward');
            $stats['reward_estimate'] = sprintf("%.8f", $estimate);

            return response($stats, Response::HTTP_OK);

        } catch (\Exception $ex) {
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function blocks()
    {
        try {
            $blocks = MiningBlock::where('is_mature', '>=', 0)->orderBy('date_found', 'DESC');
            return response($blocks->paginate(10), Response::HTTP_OK);

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

}
