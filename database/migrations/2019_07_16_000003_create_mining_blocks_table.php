<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMiningBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mining_blocks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mining_pool_id')->unsigned();
            $table->integer('block')->unsigned();
            $table->boolean('is_mature')->default(false);
            $table->dateTime('date_found');
            $table->dateTime('date_started');
            $table->text('hash');
            $table->integer('confirmations')->unsigned()->default(0);
            $table->text('total_shares')->nullable();
            $table->string('total_score');
            $table->string('reward');
            $table->integer('mining_duration')->unsigned()->default(0);
            $table->string('nmc_reward');
            $table->boolean('is_paid')->default(false);
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
        Schema::dropIfExists('mining_blocks');
    }
}
