<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMiningPoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mining_pools', function (Blueprint $table) {
            $table->increments('id');
            $table->string('unconfirmed_reward')->default(0);
            $table->string('rating')->default(0);
            $table->string('nmc_send_threshold')->default(0);
            $table->string('unconfirmed_nmc_reward')->default(0);
            $table->string('estimated_reward')->default(0);
            $table->string('hashrate')->default(0);
            $table->string('confirmed_nmc_reward')->default(0);
            $table->string('send_threshold')->default(0);
            $table->string('confirmed_reward')->default(0);

            $table->string('active_workers')->nullable();
            $table->datetime('round_started')->nullable();
            $table->string('luck_30')->nullable();
            $table->string('shares_cdf')->nullable();
            $table->string('luck_b50')->nullable();
            $table->string('luck_b10')->nullable();
            $table->string('active_stratum')->nullable();
            $table->string('ghashes_ps')->nullable();
            $table->string('shares')->nullable();
            $table->time('round_duration')->nullable();
            $table->string('score')->nullable();
            $table->string('luck_b250')->nullable();
            $table->string('luck_7')->nullable();
            $table->string('luck_1')->nullable();

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
        Schema::dropIfExists('mining_pools');
    }
}


