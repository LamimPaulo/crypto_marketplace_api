<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInternalFlagToGatewayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gateway', function (Blueprint $table) {
            $table->uuid('payer_user_id')->nullable();
            $table->integer('is_internal_payment')->unsigned()->nullable();
            $table->integer('arbitrage_user_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gateway', function (Blueprint $table) {
            //
        });
    }
}
