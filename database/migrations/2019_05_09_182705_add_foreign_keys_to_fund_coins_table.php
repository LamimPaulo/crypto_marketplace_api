<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddForeignKeysToFundCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE fund_coins ADD CONSTRAINT fk_fund_coins_fund_id FOREIGN KEY (fund_id) REFERENCES funds(id);');
        DB::statement('ALTER TABLE fund_coins ADD CONSTRAINT fk_fund_coins_coin_id FOREIGN KEY (coin_id) REFERENCES coins(id);');
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
