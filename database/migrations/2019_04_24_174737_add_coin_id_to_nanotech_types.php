<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoinIdToNanotechTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nanotech_types', function (Blueprint $table) {
            $table->uuid('coin_id')->default(10);
            $table->dropColumn(['brokerage_fee']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nanotech_types', function (Blueprint $table) {
            $table->decimal('brokerage_fee', 10, 8)->default(0);
            $table->dropColumn(['coin_id']);
        });
    }
}
