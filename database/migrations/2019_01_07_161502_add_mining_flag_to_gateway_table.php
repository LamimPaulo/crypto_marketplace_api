<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMiningFlagToGatewayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gateway', function (Blueprint $table) {
            $table->uuid('mining_user_id')->nullable()->after('tax');
            $table->tinyInteger('category')->default(1)->after('tax');
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
            $table->dropColumn(['mining_user_id','category']);
        });
    }
}
