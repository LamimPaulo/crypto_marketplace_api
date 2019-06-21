<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddForeignKeysToCoinQuotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE coin_quotes ADD CONSTRAINT fk_coin_quotes_coin_id FOREIGN KEY (coin_id) REFERENCES coins(id);');
        DB::statement('ALTER TABLE coin_quotes ADD CONSTRAINT fk_coin_quotes_coin_quote_id FOREIGN KEY (quote_coin_id) REFERENCES coins(id);');
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
