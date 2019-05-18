<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExchangeTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchange_trades', function (Blueprint $table) {
            $table->increments('id');
            $table->string('client_order_id')->nullable();
            $table->string('symbol');
            $table->string('type');
            $table->enum('side', ['buy', 'sell']);
            $table->decimal('amount', 28, 18)->default(0);
            $table->decimal('price', 28, 18)->default(0);
            $table->decimal('total', 28, 18)->default(0);
            $table->string('status')->nullable();
            $table->decimal('fee', 28, 18)->default(0);
            $table->decimal('profit', 28, 18)->default(0);
            $table->decimal('profit_percent', 28, 18)->default(0);
            $table->string('base_exchange');
            $table->decimal('base_price', 28, 18)->default(0);
            $table->string('quote_exchange');
            $table->decimal('quote_price', 28, 18)->default(0);
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
        Schema::dropIfExists('exchange_trades');
    }
}
