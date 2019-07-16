<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMiningWorkersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mining_workers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mining_pool_id')->unsigned();
            $table->string('worker');
            $table->string('last_share')->default(0)->nullable();
            $table->string('score')->default(0)->nullable();
            $table->boolean('alive')->default(false);
            $table->string('shares')->default(0)->nullable();
            $table->string('hashrate')->default(0);
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
        Schema::dropIfExists('mining_workers');
    }
}
