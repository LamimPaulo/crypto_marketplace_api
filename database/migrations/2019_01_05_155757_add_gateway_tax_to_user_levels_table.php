<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGatewayTaxToUserLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_levels', function (Blueprint $table) {
            $table->boolean('is_gateway_elegible')->default(0);
            $table->decimal('gateway_tax', 10, 3)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_levels', function (Blueprint $table) {
            $table->dropColumn(['gateway_tax', 'is_gateway_elegible']);
        });
    }
}
