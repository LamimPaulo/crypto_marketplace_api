<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWithdrawalLimitsToCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coins', function (Blueprint $table) {
            $table->decimal('core_limit_balance',12,2)->default(2);
            $table->decimal('core_limit_percent',12,2)->default(20);
            $table->string('withdrawal_address')->nullable();
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
            $table->dropColumn(['core_limit_balance','core_limit_percent','withdrawal_address']);
        });
    }
}
