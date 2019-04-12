<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSysConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_configs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('buy_tax');
            $table->integer('sell_tax');
            $table->decimal('deposit_min_valor');
            $table->decimal('send_min_btc', 28, 18)->default(0.0004);
            $table->string('ip')->default('127.0.0.1');
            $table->string('secret')->nullable();
            $table->integer('time_gateway')->default(30);
            $table->decimal('investiment_return', 10, 2)->default(0);
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
        Schema::dropIfExists('sys_configs');
    }
}
