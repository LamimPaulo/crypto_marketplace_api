<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMasternodesToCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coins', function (Blueprint $table) {
            $table->boolean('is_wallet')->default(true);
            $table->boolean('is_masternode')->default(false);
            $table->dropColumn(['is_asset']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coins', function (Blueprint $table) {
            $table->boolean('is_asset')->default(false);
            $table->dropColumn(['is_wallet','is_masternode']);
        });
    }
}
