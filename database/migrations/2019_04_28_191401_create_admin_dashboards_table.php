<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminDashboardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_dashboards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('docs_unverified')->unsigned()->default(0);
            $table->integer('incomplete_users')->unsigned()->default(0);
            $table->integer('complete_users')->unsigned()->default(0);
            $table->integer('total_users')->unsigned()->default(0);
            $table->integer('withdrawals_pending_count')->unsigned()->default(0);
            $table->decimal('withdrawals_pending_amount', 18, 8)->default(0);
            $table->integer('withdrawals_success_count')->unsigned()->default(0);
            $table->decimal('withdrawals_success_amount', 18, 8)->default(0);
            $table->integer('deposits_pending_count')->unsigned()->default(0);
            $table->decimal('deposits_pending_amount', 18, 8)->default(0);
            $table->integer('deposits_success_count')->unsigned()->default(0);
            $table->decimal('deposits_success_amount', 18, 8)->default(0);
            $table->decimal('levels_amount', 18, 8)->default(0);
            $table->integer('withdrawals_nanotech_btc')->unsigned()->default(0);
            $table->decimal('withdrawals_nanotech_btc_amount', 18, 8)->default(0);
            $table->integer('withdrawals_nanotech_lqx')->unsigned()->default(0);
            $table->decimal('withdrawals_nanotech_lqx_amount', 18, 8)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_dashboards');
    }
}
