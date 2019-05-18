<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEndDateToFundBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fund_balances', function (Blueprint $table) {
            $table->dropColumn(['balance','type']);
        });

        Schema::table('fund_balances', function (Blueprint $table) {
            $table->date('end_date')->nullable();
            $table->decimal('balance_blocked', 28, 18)->default(0);
            $table->decimal('balance_free', 28, 18)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fund_balances', function (Blueprint $table) {
            $table->dropColumn(['end_date','balance_blocked','balance_blocked']);
            $table->decimal('balance', 28, 18)->default(0);
        });
    }
}
