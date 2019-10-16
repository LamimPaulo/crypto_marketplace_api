<?php

namespace App\Console\Commands;

use App\Enum\EnumMasternodeStatus;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumUserLevelLimitType;
use App\Models\Masternode;
use App\Models\Transaction;
use App\Models\User\UserLevelLimit;
use App\Models\User\UserWallet;
use App\User;
use Illuminate\Console\Command;

class RequestMasternodes extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'masternodes:request';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Faz o pedido de criação de masternodes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        $pending = Masternode::where('status', EnumMasternodeStatus::PENDING)->get();
        foreach ($pending as $masternode) {
            $this->RequestMasternode($masternode);
        }
    }

    private function RequestMasternode($masternode)
    {
        //TODO: send request to masternode
        //TODO: update request to processing
    }
}
