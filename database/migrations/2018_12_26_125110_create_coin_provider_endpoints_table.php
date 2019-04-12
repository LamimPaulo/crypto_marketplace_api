<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoinProviderEndpointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_provider_endpoints', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('endpoint');
            $table->string('method');
            $table->text('description')->nullable();
            $table->integer('provider_id')->unsigned();
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
        Schema::dropIfExists('coin_provider_endpoints');
    }
}
