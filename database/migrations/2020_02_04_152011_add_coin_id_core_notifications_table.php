<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCoinIdCoreNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('core_notifications', function (Blueprint $table) {
            $table->integer('coin_id')->unsigned()->default(10);
            $table->dropColumn(['status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('core_notifications', function (Blueprint $table) {
            $table->boolean('status')->default(true);
            $table->dropColumn(['coin_id']);
        });
    }
}
