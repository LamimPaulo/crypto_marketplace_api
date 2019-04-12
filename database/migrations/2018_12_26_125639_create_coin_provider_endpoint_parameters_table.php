<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoinProviderEndpointParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_provider_endpoint_parameters', function (Blueprint $table) {
            $table->increments('id');
            $table->string('parameter');
            $table->boolean('required')->default(1);
            $table->text('decription')->nullable();
            $table->integer('endpoint_id')->unsigned();
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
        Schema::dropIfExists('coin_provider_endpoint_parameters');
    }
}
