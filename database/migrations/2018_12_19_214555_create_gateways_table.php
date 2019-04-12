<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGatewaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gateway', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('user_id')->nullable();
            $table->integer('coin_id')->unsigned()->nullable();
            $table->tinyInteger('status');
            $table->tinyInteger('type');
            $table->string('address')->nullable();
            $table->decimal('amount', 28, 18)->nullable();
            $table->decimal('value', 28, 18)->nullable();
            $table->decimal('received', 28, 18)->nullable();
            $table->string('tx')->nullable();
            $table->string('txid')->nullable();
            $table->tinyInteger('confirmations')->default(0);
            $table->decimal('tax', 28, 18);
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
        Schema::dropIfExists('gateway');
    }
}
