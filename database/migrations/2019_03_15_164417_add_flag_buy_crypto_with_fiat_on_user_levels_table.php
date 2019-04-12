<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFlagBuyCryptoWithFiatOnUserLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_levels', function (Blueprint $table) {
            $table->boolean('is_allowed_sell_for_fiat')->default(0)->after('is_card_elegible');
            $table->boolean('is_allowed_buy_with_fiat')->default(0)->after('is_card_elegible');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_levels', function (Blueprint $table) {
            $table->dropColumn(['is_allowed_buy_with_fiat', 'is_allowed_sell_for_fiat']);
        });
    }
}
