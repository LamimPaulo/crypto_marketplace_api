<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExchangeTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchange_taxes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('exchange_id')->unsigned();
            $table->integer('coin_id')->unsigned();
            $table->tinyInteger('type')->default(1);
            $table->tinyInteger('calc_type')->default(1);
            $table->decimal('value', 18, 8)->default(0);
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
        Schema::dropIfExists('exchange_taxes');
    }
}
