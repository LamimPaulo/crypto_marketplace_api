<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoinPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('coin_id')->unsigned();
            $table->integer('provider_id')->unsigned();
            $table->string('symbol');
            $table->decimal('price_change', 28, 18)->default(0);
            $table->decimal('price_change_percent', 28, 18)->default(0);
            $table->decimal('prev_close_price', 28, 18)->default(0);
            $table->decimal('last_price', 28, 18)->default(0);
            $table->decimal('bid_price', 28, 18)->default(0);
            $table->decimal('ask_price', 28, 18)->default(0);
            $table->decimal('open_price', 28, 18)->default(0);
            $table->decimal('high_price', 28, 18)->default(0);
            $table->decimal('low_price', 28, 18)->default(0);
            $table->timestamp('opentime')->nullable();
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
        Schema::dropIfExists('binance_coins');
    }
}
