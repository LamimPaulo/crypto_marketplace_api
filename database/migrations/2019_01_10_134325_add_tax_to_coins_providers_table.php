<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxToCoinsProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coin_providers', function (Blueprint $table) {
            $table->decimal('comission', 18, 8)->default(0.1)->after('endpoint');
            $table->tinyInteger('comission_type')->default(1)->after('endpoint');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coin_providers', function (Blueprint $table) {
            $table->dropColumn(['comission', 'comission_type']);
        });
    }
}
