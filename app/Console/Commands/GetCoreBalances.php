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
            'is_crypto' => true
        ])
            ->where('id', '<>', Coin::getByAbbr('LQX')->id)->get();

        foreach ($coins as $coin) {
            try {
                $balance = OffScreenController::post(EnumOperationType::GET_BALANCE, [], $coin->abbr);
                $coin->core_balance = $balance;
                $coin->core_status = 1;
                $coin->save();
            } catch (\Exception $exception) {
                $coin->core_status = 0;
                $coin->save();

                $this->sendNotification($coin->abbr);
            }
        }

    }

    private function sendNotification($coin)
    {
        $alert = CoreNotification::where([
            'status' => false
        ])->first();

        if (!$alert) {
            $message = "Core $coin " . env("APP_NAME") . " Offline, favor reiniciar o serviço.";
            CoreNotification::create([
                'email' => 'cristianovelkan@gmail.com',
                'status' => false,
                'description' => $message
            ]);

            Mail::to([
                'cristianovelkan@gmail.com',
                'vendasnavi@hotmail.com'
            ])->send(new AlertsMail($message));
        }
    }
}
