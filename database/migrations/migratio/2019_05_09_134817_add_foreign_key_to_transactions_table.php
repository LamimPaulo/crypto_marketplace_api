<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddForeignKeyToTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE transactions ADD CONSTRAINT fk_users_id FOREIGN KEY (user_id) REFERENCES users(id);');
        DB::statement('ALTER TABLE transactions ADD CONSTRAINT fk_coins_id FOREIGN KEY (coin_id) REFERENCES coins(id);');
        DB::statement('ALTER TABLE transactions ADD CONSTRAINT fk_wallets_id FOREIGN KEY (wallet_id) REFERENCES user_wallets(id);');
        DB::statement('ALTER TABLE transactions ADD CONSTRAINT fk_sender_user_id FOREIGN KEY (user_id) REFERENCES users(id);');
        DB::statement('ALTER TABLE transactions ADD CONSTRAINT fk_system_account_id FOREIGN KEY (system_account_id) REFERENCES system_accounts(id);');
        DB::statement('ALTER TABLE transactions ADD CONSTRAINT fk_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_accounts(id);');
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
