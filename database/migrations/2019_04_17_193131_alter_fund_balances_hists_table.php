<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFundBalancesHistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('fund_balances_hists');

        Schema::create('fund_balances_hists', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fund_balance_id')->unsigned();
            $table->decimal('balance_free', 28, 18)->default(0);
            $table->decimal('balance_blocked', 28, 18)->default(0);
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
        //
    }
}
