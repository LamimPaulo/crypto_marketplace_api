<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGatewayApiKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gateway_api_keys', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('user_id')->nullable();
            $table->uuid('api_key');
            $table->uuid('secret');
            $table->ipAddress('ip');
            $table->integer('payment_coin')->unsigned()->default(1);//btc
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
        Schema::dropIfExists('gateway_api_keys');
    }
}
