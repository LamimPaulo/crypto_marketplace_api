<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInfoToGatewayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gateway', function (Blueprint $table) {
            $table->text('info')->nullable();
            $table->text('txid_reverse')->nullable();
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
            $table->dropColumn(['info', 'txid_reverse']);
        });
    }
}
