<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddForeignKeysToNanotechTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE nanotech ADD CONSTRAINT fk_nanotech_user_id FOREIGN KEY (user_id) REFERENCES users(id);');
        DB::statement('ALTER TABLE nanotech ADD CONSTRAINT fk_nanotech_coin_id FOREIGN KEY (coin_id) REFERENCES coins(id);');
        DB::statement('ALTER TABLE nanotech ADD CONSTRAINT fk_nanotech_type_id FOREIGN KEY (type_id) REFERENCES nanotech_types(id);');
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
