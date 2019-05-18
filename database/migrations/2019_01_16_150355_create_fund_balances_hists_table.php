<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFundBalancesHistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fund_balances_hists', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('user_id');
            $table->integer('fund_id')->unsigned();
            $table->decimal('balance', 28, 18)->default(0);
            $table->enum('type', [0, 1])
                ->default(0);
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
        Schema::dropIfExists('fund_balances_hists');
    }
}
