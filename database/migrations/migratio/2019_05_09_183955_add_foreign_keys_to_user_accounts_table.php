<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddForeignKeysToUserAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE user_accounts ADD CONSTRAINT fk_user_accounts_user_id FOREIGN KEY (user_id) REFERENCES users(id);');
        DB::statement('ALTER TABLE user_accounts ADD CONSTRAINT fk_user_accounts_bank_id FOREIGN KEY (bank_id) REFERENCES banks(id);');
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
