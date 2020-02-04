<?php

namespace App\Console\Commands;

use App\Enum\EnumOperationType;
use App\Http\Controllers\OffScreenController;
use App\Mail\AlertsMail;
use App\Models\Admin\CoreNotification;
use App\Models\Coin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class GetCoreBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:corebalances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Core Balance';

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
     *
     */
    public function handle()
    {
        $coins = Coin::where([
            'is_wallet' => true,
            'is_crypto' => true,
            'is_active' => true
        ])
            ->where('id', '<>', Coin::getByAbbr('LQXD')->id)->get();

        foreach ($coins as $coin) {
            try {
                $balance = OffScreenController::post(EnumOperationType::GET_BALANCE, [], $coin->abbr);
                $coin->core_balance = $balance;
                $coin->core_status = 1;
                $coin->save();

                $alert = CoreNotification::where('coin_id', $coin->id)->get();
                $alert->each(function ($a) {
                    $a->delete();
                });

            } catch (\Exception $exception) {
                $coin->core_status = 0;
                $coin->save();

                $this->sendNotification($coin);
            }
        }

    }

    private function sendNotification($coin)
    {
        $alert = CoreNotification::where([
            'coin_id' => $coin->id
        ])->first();

        if (!$alert) {
            $message = "Core {$coin->abbr} " . env("APP_NAME") . " fora do ar, envios pausados, favor verificar e reiniciar o serviço.";

            $emails = explode(",", env('DEV_MAIL'));
            CoreNotification::create([
                'email' => $emails[0],
                'coin_id' => $coin->id,
                'description' => $message
            ]);

            foreach ($emails as $email) {
                Mail::to($email)->send(new AlertsMail($message));
            }
        }
    }
}
