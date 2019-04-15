<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLimitLqxToUserLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_levels', function (Blueprint $table) {
            $table->decimal('limit_lqx_diary', 28, 18)->default(0);
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
            $table->dropColumn('limit_lqx_diary');
        });
    }
}
