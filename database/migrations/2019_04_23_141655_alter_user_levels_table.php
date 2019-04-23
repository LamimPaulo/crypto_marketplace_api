<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_levels', function (Blueprint $table) {
            $table->dropColumn(['is_gateway_elegible', 'gateway_tax', 'limit_lqx_diary', 'is_card_elegible']);

            $table->decimal('nanotech_lqx_fee', 6, 3)->default(0);
            $table->decimal('nanotech_btc_fee', 6, 3)->default(0);
            $table->decimal('masternode_fee', 6, 3)->default(0);
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
            $table->boolean('is_gateway_elegible')->default(0);
            $table->decimal('gateway_tax', 10, 3)->default(0);
            $table->decimal('limit_lqx_diary', 10, 3)->default(0);
            $table->boolean('is_card_elegible')->default(0);

            $table->dropColumn(['nanotech_lqx_fee', 'nanotech_btc_fee', 'masternode_fee']);
        });
    }
}
