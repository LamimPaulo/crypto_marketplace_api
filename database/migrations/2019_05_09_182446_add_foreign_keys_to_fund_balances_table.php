<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddForeignKeysToFundBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE fund_balances ADD CONSTRAINT fk_fund_balances_funds_id FOREIGN KEY (fund_id) REFERENCES funds(id);');
        DB::statement('ALTER TABLE fund_balances ADD CONSTRAINT fk_fund_balances_users_id FOREIGN KEY (user_id) REFERENCES users(id);');
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
