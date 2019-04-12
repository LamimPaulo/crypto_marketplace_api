<?php

namespace App\Console\Commands;

use App\Enum\EnumGatewayStatus;
use App\Models\Gateway;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GatewayExpirePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gateway:expirepayments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar Pagamentos do Gateway e setar como expirados de acordo com o tempo de atualização';

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
        $this->updatePayments();
    }

    private function updatePayments()
    {
        $payments = Gateway::whereIn('status', [EnumGatewayStatus::NEWW, EnumGatewayStatus::RECENT])->get();
        foreach ($payments as $payment) {
            if (Carbon::now()->gt($payment->time_limit)) {
                $payment->status = EnumGatewayStatus::EXPIRED;
                $payment->save();
            }
        }

    }
}
