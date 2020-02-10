<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMasternodeImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('masternode_imports', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('months');
            $table->string('reward_address');
            $table->string('email');
            $table->boolean('is_sync')->default(false);
            $table->boolean('is_rewarded')->default(false);
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
        Schema::dropIfExists('masternode_imports');
    }
}
