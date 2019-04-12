<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMiningQuotasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mining_quotas', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('user_id')->nullable();
            $table->integer('mining_plan_id')->unsigned()->nullable();
            $table->integer('ths_quota')->unsigned();
            $table->decimal('buy_price', 28, 18)->default(0);
            $table->decimal('sell_price', 28, 18)->default(0);
            $table->decimal('market_price', 28, 18)->default(0);
            $table->boolean('is_buying')->default(false);
            $table->boolean('is_selling')->default(false);
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
        Schema::dropIfExists('mining_quotas');
    }
}
