<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableSysConfigs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sys_configs', function (Blueprint $table) {
            $table->dropColumn(['investiment_return']);

            $table->time('min_withdrawal_hour')->default('09:00:00');
            $table->time('max_withdrawal_hour')->default('16:00:00');
            $table->string('withdrawal_days')->default('1,2,3,4,5');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sys_configs', function (Blueprint $table) {
            $table->decimal('investiment_return', 10, 2)->default(0);
            $table->dropColumn(['min_withdrawal_hour', 'max_withdrawal_hour', 'withdrawal_days']);
        });
    }
}
