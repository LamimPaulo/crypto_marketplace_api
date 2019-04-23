<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMasternodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('masternodes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('coin_id')->unsigned();
            $table->decimal("roi", 18, 8)->default(0);
            $table->decimal("daily_return", 18 ,8)->default(0);
            $table->decimal("daily_return_btc", 18 ,8)->default(0);
            $table->timestamps();
        });

        Schema::create('masternode_hists', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('coin_id')->unsigned();
            $table->decimal("roi", 18, 8)->default(0);
            $table->decimal("daily_return", 18 ,8)->default(0);
            $table->decimal("daily_return_btc", 18 ,8)->default(0);
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
        Schema::dropIfExists('masternodes');
        Schema::dropIfExists('masternode_hists');
    }
}
