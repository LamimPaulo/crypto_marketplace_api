<?php

namespace App\Console\Commands;

use App\Models\Nanotech\NanotechProfitPercent;
use App\Models\Nanotech\NanotechType;
use Illuminate\Console\Command;

class NanotechPercentages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nanotech:percentages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera as porcentagens diárias de lucro do mês';

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
     */
    public function handle()
    {
        $this->generate();
    }

    private function generate()
    {
        $investmentReturn = NanotechType::all();
        $days = 31;
        foreach ($investmentReturn as $ir) {
            $random = $this->_random_numbers_sum(31, $ir->montly_return * 1000);
            for ($i = 0; $i < $days; $i++) {
                $percentage = NanotechProfitPercent::firstOrNew([
                    'day' => date('Y-m-d', strtotime("+$i days")),
                    'type_id' => $ir->id
                ]);

                $percentage->percent = abs($random[$i] / 1000);
                $percentage->save();
            }
        }
    }

    private function _random_numbers_sum($num_numbers = 30, $total = 500)
    {
        $numbers = [];

        $loose_pcc = $total / $num_numbers;

        for ($i = 1; $i < $num_numbers; $i++) {
            $ten_pcc = $loose_pcc * 0.7;
            $rand_num = mt_rand(($loose_pcc - $ten_pcc), ($loose_pcc + $ten_pcc));

            $numbers[] = $rand_num;
        }

        $numbers_total = array_sum($numbers);

        $numbers[] = $total - $numbers_total;

        return $numbers;
    }
}
