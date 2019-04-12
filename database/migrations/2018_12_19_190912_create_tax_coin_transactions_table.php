<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxCoinTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_coin_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tax_coin_id')->unsigned();
            $table->integer('operation_type')->unsigned();
            $table->integer('operation_id')->unsigned();
            $table->decimal('crypto', 28, 18)->default(0);
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
        Schema::dropIfExists('tax_coin_transactions');
    }
}
