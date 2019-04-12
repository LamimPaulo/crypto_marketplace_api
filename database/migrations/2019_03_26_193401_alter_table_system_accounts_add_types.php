<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableSystemAccountsAddTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('system_accounts', function (Blueprint $table) {
            $table->tinyInteger('category')->default(1);
        });

        Schema::table('user_accounts', function (Blueprint $table) {
            $table->tinyInteger('category')->default(1);
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
