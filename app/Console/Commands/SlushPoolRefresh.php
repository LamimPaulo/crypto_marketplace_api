<?php

namespace App\Console\Commands;

use App\Models\Mining\MiningBlock;
use App\Models\Mining\MiningPool;
use App\Models\Mining\MiningWorker;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SlushPoolRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pool:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Slush Pool Stats';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $this->blocks();
        $this->workers();
    }

    public function blocks()
    {
        try {
            $api = new \GuzzleHttp\Client();
            $response = $api->get(config('services.slushpool.stats'),
                [
                    "headers" => [
                        "X-SlushPool-Auth-Token" => config('services.slushpool.key')
                    ]
                ]);
            $result = json_decode($response->getBody()->getContents());

            $miningPool = MiningPool::firstOrNew(['id' => 1]);
            $miningPool->active_workers = $result->btc->pool_active_workers;
            $miningPool->round_started = Carbon::createFromTimestamp($result->btc->round_started)->toDateTimeString();
            $miningPool->shares_cdf = $result->btc->round_probability;
            $miningPool->luck_b10 = $result->btc->luck_b10;
            $miningPool->luck_b50 = $result->btc->luck_b50;
            $miningPool->luck_b250 = $result->btc->luck_b250;
            $miningPool->round_duration = Carbon::createFromTimestamp($result->btc->round_duration)->toTimeString();
//            $miningPool->active_stratum = $result->btc->active_stratum;
//            $miningPool->ghashes_ps = $result->btc->ghashes_ps;
//            $miningPool->shares = $result->btc->shares;
            $miningPool->score = $result->btc->pool_scoring_hash_rate;
            $miningPool->save();

            foreach ($result->btc->blocks as $i => $b) {
                $block = MiningBlock::firstOrNew(['block' => $i]);
                $block->mining_pool_id = 1;
                $block->is_mature       = $b->state == "confirmed" ? 1 : 0;
                $block->date_found      = Carbon::createFromTimestamp($b->date_found)->toDateTimeString();
                $block->date_started    = Carbon::createFromTimestamp($b->date_found )->toDateTimeString();
                $block->hash            = '';
                $block->confirmations   = $b->confirmations_left;
                $block->total_shares    = $b->total_shares;
                $block->total_score     = $b->pool_scoring_hash_rate;
                $block->reward          = $b->user_reward;
                $block->mining_duration = 1;
                $block->nmc_reward      = 0;
                $block->save();
            }

        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    private function workers()
    {
        try {
            $api = new \GuzzleHttp\Client();
            $response = $api->get(config('services.slushpool.workers'),
                [
                    "headers" => [
                        "X-SlushPool-Auth-Token" => config('services.slushpool.key')
                    ]
                ]);
            $result = json_decode($response->getBody()->getContents());

//            $miningPool = MiningPool::firstOrNew(['id' => 1]);
//            $miningPool->unconfirmed_reward = $result->unconfirmed_reward;
//            $miningPool->rating = $result->rating;
//            $miningPool->nmc_send_threshold = $result->nmc_send_threshold;
//            $miningPool->unconfirmed_nmc_reward = $result->unconfirmed_nmc_reward;
//            $miningPool->estimated_reward = $result->estimated_reward;
//            $miningPool->hashrate = $result->hashrate;
//            $miningPool->confirmed_nmc_reward = $result->confirmed_nmc_reward;
//            $miningPool->send_threshold = $result->send_threshold;
//            $miningPool->confirmed_reward = $result->confirmed_reward;
//            $miningPool->save();

            foreach ($result->btc->workers as $i => $w) {
                $worker = MiningWorker::firstOrNew(['worker' => $i]);
                $worker->mining_pool_id = 1;
                $worker->last_share = $w->last_share;
                $worker->score = $w->hash_rate_scoring;
                $worker->alive = $w->state == "OK" ? 1 : 0;
//                $worker->shares = $w->shares;
                $worker->hashrate = $w->hash_rate_5m;
                $worker->save();
            }

        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
}
