<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddForeignKeysToFundTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE fund_transactions ADD CONSTRAINT fk_fund_transactions_transactions_id FOREIGN KEY (transaction_id) REFERENCES transactions(id);');
        DB::statement('ALTER TABLE fund_transactions ADD CONSTRAINT fk_fund_transactions_users_id FOREIGN KEY (user_id) REFERENCES users(id);');
        DB::statement('ALTER TABLE fund_transactions ADD CONSTRAINT fk_fund_transactions_coins_id FOREIGN KEY (coin_id) REFERENCES coins(id);');
        DB::statement('ALTER TABLE fund_transactions ADD CONSTRAINT fk_fund_transactions_funds_id FOREIGN KEY (fund_id) REFERENCES funds(id);');
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
