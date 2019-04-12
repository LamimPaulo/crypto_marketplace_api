<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFlagsToCoinPairsTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::table('coin_pairs', function (Blueprint $table) {
            $table->boolean('is_trade_option')->default(0)->after('min_trade_amount');
            $table->boolean('is_asset_option')->default(0)->after('min_trade_amount');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('coin_pairs', function (Blueprint $table) {
            $table->dropColumn(['is_trade_option', 'is_asset_option']);
        });
    }
}
