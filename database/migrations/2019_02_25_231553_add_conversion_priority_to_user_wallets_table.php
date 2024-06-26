<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConversionPriorityToUserWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_wallets', function (Blueprint $table) {
            $table->tinyInteger('conversion_priority')->default(0);
            $table->boolean('sync')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_wallets', function (Blueprint $table) {
            $table->dropColumn(['conversion_priority','sync']);
        });
    }
}
