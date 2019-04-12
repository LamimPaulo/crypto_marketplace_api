<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFeesInCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coins', function (Blueprint $table) {
            $table->dropColumn(['fee']);
            $table->dropColumn(['confirmations']);
            $table->decimal('fee_high', 28, 18)->default(0)->after('decimal');
            $table->decimal('fee_avg', 28, 18)->default(0)->after('decimal');
            $table->decimal('fee_low', 28, 18)->default(0)->after('decimal');
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
            $table->dropColumn(['fee_high','fee_avg','fee_low']);
            $table->decimal('fee', 28 ,18)->default(0);
            $table->integer('confirmations')->unsigned()->default(6);
        });
    }
}
