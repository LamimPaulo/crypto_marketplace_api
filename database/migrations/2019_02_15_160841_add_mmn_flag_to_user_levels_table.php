<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMmnFlagToUserLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_levels', function (Blueprint $table) {
            $table->boolean('is_card_elegible')->default(0)->after('referral_profit');
            $table->decimal('gateway_mmn_tax', 10, 3)->default(0)->after('referral_profit');
            $table->boolean('is_gateway_mmn_elegible')->default(0)->after('referral_profit');
            $table->boolean('is_gateway_elegible')->default(0)->after('referral_profit');
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
            $table->dropColumn(['is_card_elegible', 'gateway_mmn_tax', 'is_gateway_mmn_elegible', 'is_gateway_elegible']);
        });
    }
}
