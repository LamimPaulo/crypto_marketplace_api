<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAdminDashboardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_dashboards', function (Blueprint $table) {
            $table->dropColumn(['general_json', 'dev_json']);
        });

        Schema::table('admin_dashboards', function (Blueprint $table) {
            $table->decimal('nanotech_lqx', 18, 8)->default(0);
            $table->decimal('nanotech_btc', 18, 8)->default(0);
            $table->decimal('masternode', 18, 8)->default(0);
            $table->integer('users')->unsigned()->default(0);
            $table->integer('incomplete_users')->unsigned()->default(0);
            $table->integer('unverified_docs')->unsigned()->default(0);
            $table->integer('levels')->unsigned()->default(0);
            $table->decimal('levels_sold', 18, 8)->default(0);
            $table->integer('levels_lqx')->unsigned()->default(0);
            $table->decimal('levels_lqx_sold', 18, 8)->default(0);
            $table->integer('deposits')->unsigned()->default(0);
            $table->decimal('deposits_amount', 18, 8)->default(0);
            $table->integer('deposits_pending')->unsigned()->default(0);
            $table->decimal('deposits_pending_amount', 18, 8)->default(0);
            $table->integer('deposits_rejected')->unsigned()->default(0);
            $table->decimal('deposits_rejected_amount', 18, 8)->default(0);
            $table->integer('deposits_paid')->unsigned()->default(0);
            $table->decimal('deposits_paid_amount', 18, 8)->default(0);
            $table->integer('withdrawals')->unsigned()->default(0);
            $table->decimal('withdrawals_amount', 18, 8)->default(0);
            $table->integer('withdrawals_pending')->unsigned()->default(0);
            $table->decimal('withdrawals_pending_amount', 18, 8)->default(0);
            $table->integer('withdrawals_paid')->unsigned()->default(0);
            $table->decimal('withdrawals_paid_amount', 18, 8)->default(0);
            $table->integer('withdrawals_processing')->unsigned()->default(0);
            $table->decimal('withdrawals_processing_amount', 18, 8)->default(0);
            $table->integer('withdrawals_reversed')->unsigned()->default(0);
            $table->decimal('withdrawals_reversed_amount', 18, 8)->default(0);
            $table->decimal('balance_brl', 18, 8)->default(0);
            $table->text('crypto_operations')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
