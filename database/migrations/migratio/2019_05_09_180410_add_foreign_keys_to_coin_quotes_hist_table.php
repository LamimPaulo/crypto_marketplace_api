<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddForeignKeysToCoinQuotesHistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE coin_quotes_hist ADD CONSTRAINT fk_coin_quotes_hist_coin_id FOREIGN KEY (coin_id) REFERENCES coins(id);');
        DB::statement('ALTER TABLE coin_quotes_hist ADD CONSTRAINT fk_coin_quotes_hist_coin_quote_id FOREIGN KEY (quote_coin_id) REFERENCES coins(id);');
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
