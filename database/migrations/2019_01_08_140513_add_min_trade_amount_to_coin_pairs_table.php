<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMinTradeAmountToCoinPairsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coin_pairs', function (Blueprint $table) {
            $table->decimal('min_trade_amount', 28, 18)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coin_pairs', function (Blueprint $table) {
            $table->dropColumn('min_trade_amount');
        });
    }
}
