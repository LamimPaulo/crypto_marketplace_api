<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddForeignKeysToUserWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DELETE FROM user_wallets WHERE user_id NOT IN (SELECT id FROM users);');

        DB::statement('ALTER TABLE user_wallets ADD CONSTRAINT fk_user_wallet_user_id FOREIGN KEY (user_id) REFERENCES users(id);');

        DB::statement('ALTER TABLE user_wallets ADD CONSTRAINT fk_user_wallet_coin_id FOREIGN KEY (coin_id) REFERENCES coins(id);');
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
