<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoinQuotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_quotes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('coin_id')->unsigned();
            $table->integer('quote_coin_id')->unsigned();
            $table->decimal('average_quote', 10, 2)->default(0);
            $table->decimal('last_quote', 10, 2)->default(0);
            $table->decimal('buy_quote', 10, 2)->default(0);
            $table->decimal('sell_quote', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('coin_quotes_hist', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('coin_id')->unsigned();
            $table->integer('quote_coin_id')->unsigned();
            $table->decimal('average_quote', 10, 2)->default(0);
            $table->decimal('buy_quote', 10, 2)->default(0);
            $table->decimal('sell_quote', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coin_quotes');
        Schema::dropIfExists('coin_quotes_hist');
    }
}
