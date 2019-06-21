<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddForeignKeysToFundBalancesHistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE fund_balances_hists ADD CONSTRAINT fk_fund_balances_hits_id FOREIGN KEY (fund_balance_id) REFERENCES fund_balances(id);');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
