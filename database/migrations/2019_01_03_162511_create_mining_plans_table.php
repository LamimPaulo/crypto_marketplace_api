<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMiningPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mining_plans', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('ths_total');
            $table->integer('ths_quota')->unsigned();
            $table->decimal('ths_quota_price', 28, 18)->default(0);
            $table->integer('ths_quota_price_type')->unsigned()->default(1);
            $table->decimal('profit', 28, 18)->default(0);
            $table->integer('profit_type')->unsigned()->default(1);
            $table->decimal('profit_payout', 28, 18)->default(0);
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
        Schema::dropIfExists('mining_plans');
    }
}
