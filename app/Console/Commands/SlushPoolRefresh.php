<?php

namespace App\Console\Commands;

use App\Models\Mining\MiningBlock;
use App\Models\Mining\MiningPool;
use App\Models\Mining\MiningWorker;
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
            $response = $api->get(config('services.slushpool.stats'));
            $result = json_decode($response->getBody()->getContents());

            $miningPool = MiningPool::firstOrNew(['id'=>1]);
            $miningPool->active_workers = $result->active_workers;
            $miningPool->round_started = $result->round_started;
            $miningPool->luck_30 = $result->luck_30;
            $miningPool->shares_cdf = $result->shares_cdf;
            $miningPool->luck_b50 = $result->luck_b50;
            $miningPool->luck_b10 = $result->luck_b10;
            $miningPool->active_stratum = $result->active_stratum;
            $miningPool->ghashes_ps = $result->ghashes_ps;
            $miningPool->shares = $result->shares;
            $miningPool->round_duration = $result->round_duration;
            $miningPool->score = $result->score;
            $miningPool->luck_b250 = $result->luck_b250;
            $miningPool->luck_7 = $result->luck_7;
            $miningPool->luck_1 = $result->luck_1;
            $miningPool->save();

            foreach($result->blocks as $i => $b){
                $block = MiningBlock::firstOrNew(['block'=>$i]);
                $block->mining_pool_id = 1;
                $block->is_mature = $b->is_mature;
                $block->date_found = $b->date_found;
                $block->date_started = $b->date_started;
                $block->hash = $b->hash;
                $block->confirmations = $b->confirmations;
                $block->total_shares = $b->total_shares;
                $block->total_score = $b->total_score;
                $block->reward = $b->reward;
                $block->mining_duration = $b->mining_duration;
                $block->nmc_reward = $b->nmc_reward;
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
            $response = $api->get(config('services.slushpool.workers'));
            $result = json_decode($response->getBody()->getContents());

            $miningPool = MiningPool::firstOrNew(['id'=>1]);
            $miningPool->unconfirmed_reward = $result->unconfirmed_reward;
            $miningPool->rating = $result->rating;
            $miningPool->nmc_send_threshold = $result->nmc_send_threshold;
            $miningPool->unconfirmed_nmc_reward = $result->unconfirmed_nmc_reward;
            $miningPool->estimated_reward = $result->estimated_reward;
            $miningPool->hashrate = $result->hashrate;
            $miningPool->confirmed_nmc_reward = $result->confirmed_nmc_reward;
            $miningPool->send_threshold = $result->send_threshold;
            $miningPool->confirmed_reward = $result->confirmed_reward;
            $miningPool->save();

            foreach ($result->workers as $i => $w) {
                $worker = MiningWorker::firstOrNew(['worker' => $i]);
                $worker->mining_pool_id = 1;
                $worker->last_share = $w->last_share;
                $worker->score = $w->score;
                $worker->alive = $w->alive;
                $worker->shares = $w->shares;
                $worker->hashrate = $w->hashrate;
                $worker->save();
            }

        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
}
