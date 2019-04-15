<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id')->unsigned()->nullable();
            $table->string('symbol');
            $table->integer('order_id')->unsigned();
            $table->string('client_order_id');
            $table->decimal('price', 28, 18)->default(0);
            $table->decimal('orig_qty', 28, 18)->default(0);
            $table->decimal('executed_qty', 28, 18)->default(0);
            $table->decimal('cummulative_quote_qty', 28, 18)->default(0);
            $table->enum('status', ['NEW', 'PARTIALLY_FILLED', 'FILLED', 'CANCELED', 'REJECTED', 'EXPIRED'])->default('NEW');
            $table->enum('time_in_force', ['GTC', 'IOC', 'FOK'])->default('FOK');
            $table->enum('type', ['MARKET', 'LIMIT', 'LIMIT_MAKER'])->default('MARKET');
            $table->enum('side', ['BUY', 'SELL']);
            $table->decimal('stop_price', 28, 18)->default(0);
            $table->decimal('iceberg_qty', 28, 18)->default(0);
            $table->string('time');
            $table->string('update_time')->nullable();
            $table->boolean('is_working')->default(1);
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
        Schema::dropIfExists('orders');
    }
}
