<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterGatewayApiKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gateway_api_keys', function (Blueprint $table) {
            $table->string('device_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('activation_code')->nullable();
            $table->string('infinitepay_wallet')->nullable();
            $table->boolean('status')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gateway_api_keys', function (Blueprint $table) {
            $table->dropColumn([
                'device_number',
                'serial_number',
                'activation_code',
                'infinitepay_wallet',
                'status'
            ]);
        });
    }
}
