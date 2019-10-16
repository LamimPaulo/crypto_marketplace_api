<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewMasternodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('masternodes');
        Schema::dropIfExists('masternode_hists');

        Schema::create('masternodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('coin_id')->unsigned();
            $table->uuid('user_id');
            $table->string('ip')->nullable();
            $table->string('reward_address');
            $table->string('payment_address')->nullable();
            $table->string('fee_address')->nullable();
            $table->tinyInteger('status');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('masternodes');
    }
}
